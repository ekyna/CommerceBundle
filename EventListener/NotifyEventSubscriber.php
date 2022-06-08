<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\NotifyModelInterface;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Bundle\CommerceBundle\Service\Notify\RecipientHelper;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentHelper;
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
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;
use League\Uri\Uri;
use League\Uri\UriModifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function urlencode;

/**
 * Class NotifyEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyEventSubscriber implements EventSubscriberInterface
{
    private ResourceRepositoryInterface $modelRepository;
    private RecipientHelper             $recipientHelper;
    private ShipmentHelper              $shipmentHelper;
    private UrlGeneratorInterface       $urlGenerator;
    private TranslatorInterface         $translator;
    private ?LoginLinkHandlerInterface  $loginLinkHandler;


    public function __construct(
        ResourceRepositoryInterface $modelRepository,
        RecipientHelper             $recipientHelper,
        ShipmentHelper              $shipmentHelper,
        UrlGeneratorInterface       $urlGenerator,
        TranslatorInterface         $translator,
        ?LoginLinkHandlerInterface  $loginLinkHandler
    ) {
        $this->modelRepository = $modelRepository;
        $this->recipientHelper = $recipientHelper;
        $this->shipmentHelper = $shipmentHelper;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->loginLinkHandler = $loginLinkHandler;
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

        $isManual = $notify->getType() === NotificationTypes::MANUAL;

        // Sender
        if ($isManual && (null !== $recipient = $this->recipientHelper->createCurrentUserRecipient())) {
            $notify->setFrom($recipient);
        } else {
            $notify->setFrom($this->recipientHelper->createWebsiteRecipient());
        }

        // Supplier order case
        if ($source instanceof SupplierOrderInterface) {
            // Recipient
            if (null !== $supplier = $source->getSupplier()) {
                $notify->addRecipient($this->recipientHelper->createRecipient($supplier, Recipient::TYPE_SUPPLIER));
            }

            return;
        }

        // Sale case
        if (null === $sale = $this->getSaleFromEvent($event)) {
            return;
        }

        // Recipient
        if (null === $customer = $sale->getCustomer()) {
            $notify->addRecipient($this->recipientHelper->createRecipient($sale, Recipient::TYPE_CUSTOMER));

            return;
        }

        if ($isManual || in_array($notify->getType(), $customer->getNotifications(), true)) {
            $notify->addRecipient($this->recipientHelper->createRecipient($customer, Recipient::TYPE_CUSTOMER));
        }

        if ($isManual) {
            return;
        }

        foreach ($customer->getContacts() as $contact) {
            if (!in_array($notify->getType(), $contact->getNotifications(), true)) {
                continue;
            }

            $notify->addRecipient($this->recipientHelper->createRecipient($contact, Recipient::TYPE_CONTACT));
            $notify->setUnsafe(true);
        }

        if (!$sale instanceof OrderInterface) {
            return;
        }

        if (null === $origin = $sale->getOriginCustomer()) {
            return;
        }

        if (in_array($notify->getType(), $origin->getNotifications(), true)) {
            $notify->addRecipient($this->recipientHelper->createRecipient($origin, Recipient::TYPE_SALESMAN));
            $notify->setUnsafe(true);
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
                throw new InvalidArgumentException('Unexpected sale class.');
            }

            $type = $this->translator->trans($type . '.label.singular', [], 'EkynaCommerce', $locale);

            $notify->setSubject(
                $this->translator->trans('notify.type.manual.subject', [
                    '%type%'   => mb_strtolower($type),
                    '%number%' => $sale->getNumber(),
                ], 'EkynaCommerce', $locale)
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

        if (null === $notify->getSource()) {
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

            case NotificationTypes::PAYMENT_AUTHORIZED:
            case NotificationTypes::PAYMENT_CAPTURED:
            case NotificationTypes::PAYMENT_EXPIRED:
                $this->buildPaymentContent($event, $model);
                break;

            case NotificationTypes::SHIPMENT_READY:
            case NotificationTypes::SHIPMENT_COMPLETE:
            case NotificationTypes::SHIPMENT_PARTIAL:
            case NotificationTypes::RETURN_PENDING:
            case NotificationTypes::RETURN_RECEIVED:
                $this->buildShipmentContent($event, $model);
                break;

            case NotificationTypes::INVOICE_COMPLETE:
            case NotificationTypes::INVOICE_PARTIAL:
                $this->buildInvoiceContent($event, $model);
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

        if (
            $source instanceof PaymentInterface
            || $source instanceof ShipmentInterface
            || $source instanceof InvoiceInterface
        ) {
            $source = $source->getSale();
        }

        if ($source instanceof OrderInterface) {
            $label = 'notify.message.customer_area.order';
            $route = 'ekyna_commerce_account_order_read';
        } elseif ($source instanceof QuoteInterface) {
            $label = 'notify.message.customer_area.quote';
            $route = 'ekyna_commerce_account_quote_read';
        } else {
            return;
        }

        /** @var CustomerInterface $customer */
        if (null === $customer = $source->getCustomer()) {
            return;
        }

        if (null === $user = $customer->getUser()) {
            return;
        }

        $uri = $this
            ->urlGenerator
            ->generate($route, ['number' => $source->getNumber()], UrlGeneratorInterface::ABSOLUTE_URL);

        if (!$notify->isUnsafe() && $user->isEnabled() && (null !== $this->loginLinkHandler)) {
            $loginLink = $this->loginLinkHandler->createLoginLink($user);
            $loginUri = Uri::createFromString($loginLink->getUrl());
            $uri = (string)UriModifier::appendQuery($loginUri, 'redirect_after=' . urlencode($uri));
        }

        $notify
            ->setButtonLabel($this->translator->trans($label, [], 'EkynaCommerce'))
            ->setButtonUrl($uri);
    }

    /**
     * Notify post build event handler.
     *
     * @param NotifyEvent $event
     */
    public function finalize(NotifyEvent $event): void
    {
        $notify = $event->getNotify();
        $type = $notify->getType();
        $sale = $this->getSaleFromEvent($event);
        $saleNumber = $sale ? $sale->getNumber() : '';
        $saleVoucher = $sale ? $sale->getVoucherNumber() : '';
        $locale = $sale ? $sale->getLocale() : null;

        // Subject
        if (empty($notify->getSubject())) {
            $trans = sprintf('notify.type.%s.subject', $type);
            $subject = $this->translator->trans($trans, ['%number%' => $saleNumber], 'EkynaCommerce', $locale);
            if ($trans != $subject) {
                $notify->setSubject($subject);
            }
        }

        // Message
        if (empty($notify->getCustomMessage())) {
            $trans = sprintf('notify.type.%s.message', $type);
            $message = $this->translator->trans($trans, ['%number%' => $saleNumber], 'EkynaCommerce', $locale);
            if ($trans != $message) {
                $notify->setCustomMessage($message);
            }
        }

        // Abort if subject or message is empty
        if ($notify->isEmpty()) {
            $event->abort();

            return;
        }

        // Add voucher number to subject and message
        if (!empty($saleVoucher)) {
            $voucherNotice = $this->translator->trans('notify.field.voucher', [
                '%number%' => $saleVoucher,
            ], 'EkynaCommerce', $locale);

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
                ->setButtonLabel($this->translator->trans('notify.message.customer_area.default', [], 'EkynaCommerce'))
                ->setButtonUrl($url);
        }
    }

    /**
     * Returns the notify model.
     *
     * @param Notify $notify
     *
     * @return NotifyModelInterface|null
     */
    protected function getModel(Notify $notify): ?NotifyModelInterface
    {
        if ($notify->getType() === NotificationTypes::MANUAL) {
            return null;
        }

        $criteria = ['type' => $notify->getType()];

        if (!$notify->isTest()) {
            $criteria['enabled'] = true;
        }

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
        } elseif (
            $source instanceof PaymentInterface
            || $source instanceof ShipmentInterface
            || $source instanceof InvoiceInterface
        ) {
            return $source->getSale();
        }

        return null;
    }

    /**
     * Builds sale content.
     *
     * @param NotifyEvent          $event
     * @param NotifyModelInterface $model
     */
    protected function buildOrderContent(NotifyEvent $event, NotifyModelInterface $model): void
    {
        $notify = $event->getNotify();
        $sale = $this->getSaleFromEvent($event);

        if (!$sale instanceof OrderInterface) {
            throw new UnexpectedTypeException($sale, OrderInterface::class);
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
     * @param NotifyEvent          $event
     * @param NotifyModelInterface $model
     */
    protected function buildPaymentContent(NotifyEvent $event, NotifyModelInterface $model): void
    {
        $notify = $event->getNotify();

        if (null === $payment = $notify->getSource()) {
            throw new RuntimeException('Notify source is not set.');
        }

        if (!$payment instanceof PaymentInterface) {
            throw new UnexpectedTypeException($payment, PaymentInterface::class);
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
     * @param NotifyEvent          $event
     * @param NotifyModelInterface $model
     */
    protected function buildShipmentContent(NotifyEvent $event, NotifyModelInterface $model): void
    {
        $notify = $event->getNotify();

        if (null === $shipment = $notify->getSource()) {
            throw new RuntimeException('Notify source is not set.');
        }

        if (!$shipment instanceof ShipmentInterface) {
            throw new UnexpectedTypeException($shipment, ShipmentInterface::class);
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
     * Builds invoice content.
     *
     * @param NotifyEvent          $event
     * @param NotifyModelInterface $model
     */
    protected function buildInvoiceContent(NotifyEvent $event, NotifyModelInterface $model): void
    {
        $notify = $event->getNotify();

        if (null === $invoice = $notify->getSource()) {
            throw new RuntimeException('Notify source is not set.');
        }

        if (!$invoice instanceof InvoiceInterface) {
            throw new UnexpectedTypeException($invoice, InvoiceInterface::class);
        }

        $sale = $this->getSaleFromEvent($event);

        $notify->addInvoice($invoice);

        // Custom message
        if (!empty($message = $model->getMessage())) {
            $message = str_replace('%number%', $sale->getNumber(), $message);
        }

        if (!empty($message)) {
            $notify->setCustomMessage($message);
        }

        // Payment message
        $payments = $sale->getPayments(true);
        if ($model->isPaymentMessage() && !$payments->isEmpty()) {
            $this->addPaymentMessage($notify, $payments->last());
        }

        // Shipment message
        if ($model->isShipmentMessage() && $shipment = $invoice->getShipment()) {
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
                    /** @noinspection HtmlUnknownTarget */
                    $tracking[] = sprintf('<a href="%s">%s</a>', $url, $parcel->getTrackingNumber());
                } elseif ($number = $parcel->getTrackingNumber()) {
                    $tracking[] = $number;
                }
            }
        } elseif ($url = $this->shipmentHelper->getTrackingUrl($shipment)) {
            /** @noinspection HtmlUnknownTarget */
            $tracking[] = sprintf('<a href="%s">%s</a>', $url, $shipment->getTrackingNumber());
        } elseif ($number = $shipment->getTrackingNumber()) {
            $tracking[] = $number;
        }

        if (empty($tracking)) {
            return '';
        }

        return sprintf(
            '<p>%s : %s</p>',
            $this->translator->trans('shipment.field.tracking_number', [], 'EkynaCommerce'),
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

    public static function getSubscribedEvents(): array
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
