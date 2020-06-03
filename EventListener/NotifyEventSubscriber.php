<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Entity\NotifyModel;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Bundle\CommerceBundle\Service\Notify\RecipientHelper;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentHelper;
use Ekyna\Bundle\UserBundle\Service\Security\LoginTokenManager;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Event\NotifyEvent;
use Ekyna\Component\Commerce\Common\Event\NotifyEvents;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Ekyna\Component\Commerce\Common\Model\Recipient;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class NotifyEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var ResourceRepositoryInterface
     */
    private $modelRepository;

    /**
     * @var RecipientHelper
     */
    private $recipientHelper;

    /**
     * @var ShipmentHelper
     */
    private $shipmentHelper;

    /**
     * @var LoginTokenManager
     */
    private $loginTokenManager;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param ResourceRepositoryInterface $modelRepository
     * @param RecipientHelper             $recipientHelper
     * @param ShipmentHelper              $shipmentHelper
     * @param LoginTokenManager           $loginTokenManager
     * @param UrlGeneratorInterface       $urlGenerator
     * @param TranslatorInterface         $translator
     */
    public function __construct(
        ResourceRepositoryInterface $modelRepository,
        RecipientHelper $recipientHelper,
        ShipmentHelper $shipmentHelper,
        LoginTokenManager $loginTokenManager,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator
    ) {
        $this->modelRepository   = $modelRepository;
        $this->recipientHelper   = $recipientHelper;
        $this->shipmentHelper    = $shipmentHelper;
        $this->loginTokenManager = $loginTokenManager;
        $this->urlGenerator      = $urlGenerator;
        $this->translator        = $translator;
    }

    /**
     * Notify build recipients event handler.
     *
     * @param NotifyEvent $event
     */
    public function buildRecipients(NotifyEvent $event): void
    {
        $notify = $event->getNotify();

        $source = $notify->getSource();
        if ($source instanceof SupplierOrderInterface) {
            if (null !== $recipient = $this->recipientHelper->createCurrentUserRecipient()) {
                $notify->setFrom($recipient);
            }

            if (null !== $supplier = $source->getSupplier()) {
                $notify->addRecipient($this->recipientHelper->createRecipient($supplier, Recipient::TYPE_SUPPLIER));
            }

            return;
        }

        if (null === $sale = $this->getSaleFromEvent($event)) {
            return;
        }

        // Sender
        $from = $this->recipientHelper->createWebsiteRecipient();
        if ($notify->getType() === NotificationTypes::MANUAL) {
            if ($recipient = $this->recipientHelper->createInChargeRecipient($sale)) {
                $from = $recipient;
            }
        }
        $notify->setFrom($from);

        // Recipient
        if ($customer = $sale->getCustomer()) {
            $notify->addRecipient($this->recipientHelper->createRecipient($customer, Recipient::TYPE_CUSTOMER));

            foreach ($customer->getContacts() as $contact) {
                if (!in_array($notify->getType(), $contact->getNotifications(), true)) {
                    continue;
                }

                $notify->addCopy($this->recipientHelper->createRecipient($contact, Recipient::TYPE_CUSTOMER));
            }

            if (!$sale instanceof OrderInterface) {
                return;
            }

            if (null === $origin = $sale->getOriginCustomer()) {
                return;
            }

            $originNotifyTypes = [
                NotificationTypes::ORDER_ACCEPTED,
                NotificationTypes::SHIPMENT_SHIPPED,
                NotificationTypes::SHIPMENT_PARTIAL,
            ];
            if (!in_array($notify->getType(), $originNotifyTypes, true)) {
                return;
            }

            $notify->addRecipient($this->recipientHelper->createRecipient($origin, Recipient::TYPE_SALESMAN));
        } else {
            $notify->addRecipient($this->recipientHelper->createRecipient($sale, Recipient::TYPE_CUSTOMER));
        }
    }

    /**
     * Notify build subject event handler.
     *
     * @param NotifyEvent $event
     */
    public function buildSubject(NotifyEvent $event): void
    {
        $notify = $event->getNotify();

        $source = $notify->getSource();

        if ($source instanceof SupplierOrderInterface) {
            $notify->setSubject(sprintf('Order %s', $source->getNumber()));

            return;
        }

        if (null === $sale = $this->getSaleFromEvent($event)) {
            return;
        }

        $locale = $sale->getLocale();

        if ($notify->getType() === NotificationTypes::MANUAL) {
            if ($sale instanceof OrderInterface) {
                $type = 'order';
            } elseif ($sale instanceof QuoteInterface) {
                $type = 'quote';
            } elseif ($sale instanceof CartInterface) {
                $type = 'cart';
            } else {
                throw new InvalidArgumentException("Unexpected sale class.");
            }

            $type = $this->translator->trans('ekyna_commerce.' . $type . '.label.singular', [], null, $locale);

            $notify->setSubject(
                $this->translator->trans('ekyna_commerce.notify.type.manual.subject', [
                    '%type%'   => mb_strtolower($type),
                    '%number%' => $sale->getNumber(),
                ], null, $locale)
            );

            return;
        }

        if (null === $model = $this->getModel($notify)) {
            $event->abort();

            return;
        }

        if (!empty($subject = $model->getSubject())) {
            $notify->setSubject(str_replace('%number%', $sale->getNumber(), $subject));
        }
    }

    /**
     * Notify build content event handler.
     *
     * @param NotifyEvent $event
     */
    public function buildContent(NotifyEvent $event): void
    {
        $notify = $event->getNotify();

        if ($notify->getType() === NotificationTypes::MANUAL) {
            return;
        }

        if (null === $source = $notify->getSource()) {
            return;
        }

        if (null === $model = $this->getModel($notify)) {
            $event->abort();

            return;
        }

        switch ($notify->getType()) {
            case NotificationTypes::CART_REMIND:
            case NotificationTypes::QUOTE_REMIND:
                $notify->setIncludeView(Notify::VIEW_AFTER);
                break;

            case NotificationTypes::ORDER_ACCEPTED:
                $this->buildOrderContent($event, $model);
                break;

            case NotificationTypes::PAYMENT_CAPTURED:
            case NotificationTypes::PAYMENT_EXPIRED:
                $this->buildPaymentContent($event, $model);
                break;

            case NotificationTypes::SHIPMENT_READY:
            case NotificationTypes::SHIPMENT_SHIPPED:
            case NotificationTypes::SHIPMENT_PARTIAL:
            case NotificationTypes::RETURN_PENDING:
            case NotificationTypes::RETURN_RECEIVED:
                $this->buildShipmentContent($event, $model);
                break;
        }
    }

    /**
     * Builds the notify button.
     *
     * @param NotifyEvent $event
     */
    public function buildButton(NotifyEvent $event): void
    {
        $notify = $event->getNotify();

        if (null === $source = $notify->getSource()) {
            return;
        }

        if ($source instanceof OrderInterface) {
            $label = 'ekyna_commerce.notify.message.customer_area.order';
            $route = 'ekyna_commerce_account_order_show';
        } elseif ($source instanceof QuoteInterface) {
            $label = 'ekyna_commerce.notify.message.customer_area.quote';
            $route = 'ekyna_commerce_account_quote_show';
        } else {
            return;
        }

        /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
        if (null === $customer = $source->getCustomer()) {
            return;
        }

        if (null === $user = $customer->getUser()) {
            return;
        }

        $after = $this
            ->urlGenerator
            ->generate($route, ['number' => $source->getNumber()]);

        $url = $this->loginTokenManager->generateLoginUrl($user, $after);

        $notify
            ->setButtonLabel($label)
            ->setButtonUrl($url);
    }

    /**
     * Notify post build event handler.
     *
     * @param NotifyEvent $event
     */
    public function finalize(NotifyEvent $event): void
    {
        $notify = $event->getNotify();

        $type        = $notify->getType();
        $sale        = $this->getSaleFromEvent($event);
        $saleNumber  = $sale ? $sale->getNumber() : '';
        $saleVoucher = $sale ? $sale->getVoucherNumber() : '';
        $locale      = $sale ? $sale->getLocale() : null;

        // Subject
        if (empty($notify->getSubject())) {
            $trans = sprintf('ekyna_commerce.notify.type.%s.subject', $type);
            if ($trans != $subject = $this->translator->trans($trans, ['%number%' => $saleNumber], null, $locale)) {
                $notify->setSubject($subject);
            }
        }

        // Message
        if (empty($notify->getCustomMessage())) {
            $trans = sprintf('ekyna_commerce.notify.type.%s.message', $type);
            if ($trans != $message = $this->translator->trans($trans, ['%number%' => $saleNumber], null, $locale)) {
                $notify->setCustomMessage($message);
            }
        }

        // Abort if subject or message is empty
        if ($notify->isEmpty()) {
            $event->abort();
        }

        // Add voucher number to subject and message
        if (!empty($saleVoucher)) {
            $voucherNotice = $this->translator->trans('ekyna_commerce.notify.field.voucher', [
                '%number%' => $saleVoucher,
            ], null, $locale);

            if (false === strpos($notify->getSubject(), $saleVoucher)) {
                $notify->setSubject($notify->getSubject() . ' (' . $voucherNotice . ')');
            }

            if (false === strpos($notify->getCustomMessage(), $saleVoucher)) {
                $notify->setCustomMessage($notify->getCustomMessage() . '<br>' . $voucherNotice . '.');
            }
        }

        // Default button url and label
        if (empty($notify->getButtonUrl())) {
            $url = $this
                ->urlGenerator
                ->generate('ekyna_user_account_index', [], UrlGeneratorInterface::ABSOLUTE_URL);

            $notify
                ->setButtonLabel('ekyna_commerce.notify.message.customer_area.default')
                ->setButtonUrl($url);
        }
    }

    /**
     * Returns the notify model.
     *
     * @param Notify $notify
     *
     * @return NotifyModel|null
     */
    protected function getModel(Notify $notify): ?NotifyModel
    {
        $criteria = ['type' => $notify->getType()];

        if (!$notify->isTest()) {
            $criteria['enabled'] = true;
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this
            ->modelRepository
            ->findOneBy($criteria);
    }

    /**
     * Returns the sale from the event.
     *
     * @param NotifyEvent $event
     *
     * @return SaleInterface|null
     */
    protected function getSaleFromEvent(NotifyEvent $event): ?SaleInterface
    {
        $source = $event->getNotify()->getSource();

        if ($source instanceof SaleInterface) {
            return $source;
        } elseif ($source instanceof PaymentInterface || $source instanceof ShipmentInterface) {
            return $source->getSale();
        }

        return null;
    }

    /**
     * Builds sale content.
     *
     * @param NotifyEvent $event
     * @param NotifyModel $model
     */
    protected function buildOrderContent(NotifyEvent $event, NotifyModel $model): void
    {
        $notify = $event->getNotify();
        $sale   = $this->getSaleFromEvent($event);

        if (!$sale instanceof OrderInterface) {
            throw new RuntimeException("Expected instance of " . OrderInterface::class);
        }

        // Custom message
        if (!empty($message = $model->getMessage())) {
            $notify->setCustomMessage(str_replace('%number%', $sale->getNumber(), $message));
        }

        // Payment message
        if ($model->isPaymentMessage() && !$sale->getPayments(true)->isEmpty()) {
            $this->addPaymentMessage($notify, $sale->getPayments(true)->last());
        }

        // Shipment message
        if ($model->isShipmentMessage() && !$sale->getShipments(true)->isEmpty()) {
            $this->addShipmentMessage($notify, $sale->getShipments(true)->last());
        }

        // Sale view
        if (!is_null($view = $model->getIncludeView())) {
            $notify->setIncludeView($view);
        } else {
            $notify->setIncludeView(Notify::VIEW_AFTER);
        }

        // Attachments
        if (empty($types = $model->getDocumentTypes())) {
            $types = [DocumentTypes::TYPE_CONFIRMATION];
        }

        $this->addAttachments($notify, $sale, $types);
    }

    /**
     * Builds payment content.
     *
     * @param NotifyEvent $event
     * @param NotifyModel $model
     */
    protected function buildPaymentContent(NotifyEvent $event, NotifyModel $model): void
    {
        $notify = $event->getNotify();

        if (null === $payment = $notify->getSource()) {
            throw new RuntimeException("Notify source is not set.");
        }

        if (!$payment instanceof PaymentInterface) {
            throw new RuntimeException("Expected instance of " . PaymentInterface::class);
        }

        $sale = $this->getSaleFromEvent($event);

        // Custom message
        if (!empty($message = $model->getMessage())) {
            $notify->setCustomMessage(str_replace('%number%', $sale->getNumber(), $message));
        }

        // Payment message
        if ($model->isPaymentMessage()) {
            $this->addPaymentMessage($notify, $payment);
        }

        if ($sale instanceof OrderInterface) {
            // Shipment message
            if ($model->isShipmentMessage() && 0 < $sale->getShipments(true)->count()) {
                $this->addShipmentMessage($notify, $sale->getShipments(true)->last());
            }
            // Invoices attachments
            foreach ($sale->getInvoices(true) as $invoice) {
                $notify->addInvoice($invoice);
            }
        }

        // Attachments
        if (empty($types = $model->getDocumentTypes())) {
            $types = [DocumentTypes::TYPE_CONFIRMATION];
        }
        $this->addAttachments($notify, $sale, $types);
    }

    /**
     * Builds shipment content.
     *
     * @param NotifyEvent $event
     * @param NotifyModel $model
     */
    protected function buildShipmentContent(NotifyEvent $event, NotifyModel $model): void
    {
        $notify = $event->getNotify();

        if (null === $shipment = $notify->getSource()) {
            throw new RuntimeException("Notify source is not set.");
        }

        if (!$shipment instanceof ShipmentInterface) {
            throw new RuntimeException("Expected instance of " . ShipmentInterface::class);
        }

        $sale = $this->getSaleFromEvent($event);

        $notify->addShipment($shipment);

        // Invoices
        if ($sale instanceof OrderInterface) {
            if ($sale->getState() === OrderStates::STATE_COMPLETED) {
                foreach ($sale->getInvoices() as $invoice) {
                    $notify->addInvoice($invoice);
                }
            } elseif (null !== $invoice = $shipment->getInvoice()) {
                $notify->addInvoice($invoice);
            }
        }

        // Pending return : add labels as attachments
        if ($notify->getType() === NotificationTypes::RETURN_PENDING) {
            foreach ($shipment->getLabels() as $label) {
                $notify->addLabel($label);
            }
        }

        // Custom message
        if (!empty($message = $model->getMessage())) {
            $message = str_replace('%number%', $sale->getNumber(), $message);
        }

        // Tracking numbers / urls
        if ($tracking = $this->buildTrackingMessage($shipment)) {
            $message .= $tracking;
        }

        if (!empty($message)) {
            $notify->setCustomMessage($message);
        }

        // Payment message
        if ($model->isPaymentMessage() && !$sale->getPayments(true)->isEmpty()) {
            $this->addPaymentMessage($notify, $sale->getPayments(true)->last());
        }

        // Shipment message
        if ($model->isShipmentMessage()) {
            $this->addShipmentMessage($notify, $shipment);
        }

        // Attachments
        if (!empty($types = $model->getDocumentTypes())) {
            $this->addAttachments($notify, $sale, $types);
        }
    }

    /**
     * Builds the shipment tracking message.
     *
     * @param ShipmentInterface $shipment
     *
     * @return string
     */
    protected function buildTrackingMessage(ShipmentInterface $shipment): string
    {
        $tracking = [];
        if ($shipment->hasParcels()) {
            foreach ($shipment->getParcels() as $parcel) {
                if ($url = $this->shipmentHelper->getTrackingUrl($parcel)) {
                    $tracking[] = sprintf('<a href="%s">%s</a>', $url, $parcel->getTrackingNumber());
                } elseif ($number = $parcel->getTrackingNumber()) {
                    $tracking[] = $number;
                }
            }
        } elseif ($url = $this->shipmentHelper->getTrackingUrl($shipment)) {
            $tracking[] = sprintf('<a href="%s">%s</a>', $url, $shipment->getTrackingNumber());
        } elseif ($number = $shipment->getTrackingNumber()) {
            $tracking[] = $number;
        }

        if (empty($tracking)) {
            return '';
        }

        return sprintf(
            '<p>%s : %s</p>',
            $this->translator->trans('ekyna_commerce.shipment.field.tracking_number'),
            implode(', ', $tracking)
        );
    }

    /**
     * Adds the payment message.
     *
     * @param Notify           $notify
     * @param PaymentInterface $payment
     *
     * @return bool Whether the payment message has been added.
     */
    protected function addPaymentMessage(Notify $notify, PaymentInterface $payment): bool
    {
        if (null === $message = $payment->getMethod()->getMessageByState($payment->getState())) {
            return false;
        }

        if (empty($content = $message->getContent())) { // TODO Locale
            return false;
        }

        $notify->setPaymentMessage($content);

        return true;
    }

    /**
     * Adds the shipment message.
     *
     * @param Notify            $notify
     * @param ShipmentInterface $shipment
     *
     * @return bool Whether the shipment message has been added.
     */
    protected function addShipmentMessage(Notify $notify, ShipmentInterface $shipment): bool
    {
        if (null === $message = $shipment->getMethod()->getMessageByState($shipment->getState())) {
            return false;
        }

        if (empty($content = $message->getContent())) { // TODO Locale
            return false;
        }

        $notify->setShipmentMessage($content);

        return true;
    }

    /**
     * Adds the attachments.
     *
     * @param Notify        $notify
     * @param SaleInterface $sale
     * @param array         $types
     *
     * @return bool Whether at last one attachment has been added.
     */
    protected function addAttachments(Notify $notify, SaleInterface $sale, array $types): bool
    {
        $added = false;

        foreach ($sale->getAttachments() as $attachment) {
            if (!in_array($attachment->getType(), $types, true)) {
                continue;
            }

            if ($attachment->isInternal()) {
                continue;
            }

            $notify->addAttachment($attachment);

            $added = true;
        }

        return $added;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            NotifyEvents::BUILD => [
                ['buildSubject', -1],
                ['buildRecipients', -2],
                ['buildContent', -3],
                ['buildButton', -4],
                ['finalize', -2048],
            ],
        ];
    }
}
