<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Mailer;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\AdminBundle\Service\Mailer\MailerFactory;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes as BDocumentTypes;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderAttachmentTypes;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderSubmit;
use Ekyna\Bundle\CommerceBundle\Model\TicketMessageInterface;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererInterface;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\LabelRenderer as ShipmentLabelRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Subject\LabelRenderer as SubjectLabelRenderer;
use Ekyna\Bundle\CoreBundle\Service\SwiftMailer\ImapCopyPlugin;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Component\Commerce\Common\Model\CouponInterface;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Ekyna\Component\Commerce\Common\Model\Recipient;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes as CDocumentTypes;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\PdfException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class Mailer
 * @package Ekyna\Bundle\CommerceBundle\Service\Mailer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Mailer
{
    /**
     * @var MailerFactory
     */
    protected $mailer;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var SettingsManagerInterface
     */
    protected $settingsManager;

    /**
     * @var RendererFactory
     */
    protected $rendererFactory;

    /**
     * @var ShipmentLabelRenderer
     */
    protected $shipmentLabelRenderer;

    /**
     * @var SubjectLabelRenderer
     */
    protected $subjectLabelRenderer;

    /**
     * @var SubjectHelperInterface
     */
    protected $subjectHelper;

    /**
     * @var FilesystemInterface
     */
    protected $filesystem;


    /**
     * Constructor.
     *
     * @param MailerFactory            $mailer
     * @param EngineInterface          $templating
     * @param TranslatorInterface      $translator
     * @param SettingsManagerInterface $settingsManager
     * @param RendererFactory          $rendererFactory
     * @param ShipmentLabelRenderer    $shipmentLabelRenderer
     * @param SubjectLabelRenderer     $subjectLabelRenderer
     * @param SubjectHelperInterface   $subjectHelper
     * @param FilesystemInterface      $filesystem
     */
    public function __construct(
        MailerFactory $mailer,
        EngineInterface $templating,
        TranslatorInterface $translator,
        SettingsManagerInterface $settingsManager,
        RendererFactory $rendererFactory,
        ShipmentLabelRenderer $shipmentLabelRenderer,
        SubjectLabelRenderer $subjectLabelRenderer,
        SubjectHelperInterface $subjectHelper,
        FilesystemInterface $filesystem
    ) {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->settingsManager = $settingsManager;
        $this->rendererFactory = $rendererFactory;
        $this->shipmentLabelRenderer = $shipmentLabelRenderer;
        $this->subjectLabelRenderer = $subjectLabelRenderer;
        $this->subjectHelper = $subjectHelper;
        $this->filesystem = $filesystem;
    }

    /**
     * Notifies the administrator about a potential fraudster.
     *
     * @param CustomerInterface $customer
     *
     * @return bool
     */
    public function sendAdminFraudsterAlert(CustomerInterface $customer): bool
    {
        $body = $this->templating->render('@EkynaCommerce/Email/admin_fraudster_alert.html.twig', [
            'customer' => $customer,
        ]);

        $subject = $this->translator->trans('ekyna_commerce.customer.notify.fraudster.subject');

        return 0 < $this->mailer->send($this->createMessage($subject, $body));
    }

    /**
     * Notifies the administrator about a customer registration.
     *
     * @param CustomerInterface $customer
     *
     * @return bool
     */
    public function sendAdminCustomerRegistration(CustomerInterface $customer): bool
    {
        $body = $this->templating->render('@EkynaCommerce/Email/admin_customer_registration.html.twig', [
            'customer' => $customer,
        ]);

        $subject = $this->translator->trans('ekyna_commerce.customer.notify.registration.subject');

        return 0 < $this->mailer->send($this->createMessage($subject, $body));
    }

    /**
     * Notifies the customer about his account balance.
     *
     * @param CustomerInterface $customer
     * @param array             $balance
     * @param string            $csvPath
     *
     * @return bool
     */
    public function sendCustomerBalance(CustomerInterface $customer, array $balance, string $csvPath = null): bool
    {
        if (empty($balance)) {
            return false;
        }

        $locale = $customer->getLocale();

        $subject = $this
            ->translator
            ->trans('ekyna_commerce.notify.type.balance.subject', [], null, $locale);

        $body = $this->templating->render('@EkynaCommerce/Email/customer_balance.html.twig', [
            'subject'  => $subject,
            'locale'   => $locale,
            'customer' => $customer,
            'balance'  => $balance,
        ]);

        $to = [$customer->getEmail() => $customer->getFirstName() . ' ' . $customer->getLastName()];

        $message = $this->createMessage($subject, $body, $to);

        // CSV attachment if any
        if (!empty($csvPath) && is_file($csvPath)) {
            $message->attach(
                \Swift_Attachment::newInstance(file_get_contents($csvPath), 'account-balance.csv', 'text/csv')
            );
        }

        // Trigger IMAP copy
        $message->getHeaders()->addTextHeader(ImapCopyPlugin::HEADER, 'do');

        return 0 < $this->mailer->send($message);
    }

    /**
     * Sends the supplier order submit message.
     *
     * @param SupplierOrderSubmit $submit
     *
     * @return bool
     *
     * @throws PdfException
     */
    public function sendSupplierOrderSubmit(SupplierOrderSubmit $submit): bool
    {
        $order = $submit->getOrder();

        $message = $this->createMessage($submit->getSubject(), $submit->getMessage(), $submit->getEmails());

        // Form attachment
        $renderer = $this->rendererFactory->createRenderer($order);
        $message->attach(\Swift_Attachment::newInstance(
            $renderer->render(RendererInterface::FORMAT_PDF),
            $order->getNumber() . '.pdf',
            'application/pdf'
        ));

        // Subjects labels
        if ($submit->isSendLabels()) {
            $subjects = [];
            foreach ($order->getItems() as $item) {
                $subjects[] = $this->subjectHelper->resolve($item);
            }

            $labels = $this->subjectLabelRenderer->buildLabels($subjects);

            $date = $order->getOrderedAt() ?? new \DateTime();
            $extra = sprintf('%s (%s)', $order->getNumber(), $date->format('Y-m-d'));
            foreach ($labels as $label) {
                $label->setExtra($extra);
            }

            $message->attach(\Swift_Attachment::newInstance(
                $this->subjectLabelRenderer->render($labels),
                'labels.pdf',
                'application/pdf'
            ));
        }

        // Trigger imap copy
        $message->getHeaders()->addTextHeader(ImapCopyPlugin::HEADER, 'do');

        return 0 < $this->mailer->send($message);
    }

    /**
     * Notifies the customer about the ticket message creation or update.
     *
     * @param TicketMessageInterface $message
     *
     * @return bool
     */
    public function sendTicketMessageToCustomer(TicketMessageInterface $message): bool
    {
        if ($message->isCustomer()) {
            throw new LogicException("Expected admin message.");
        }

        if (!$message->isNotify()) {
            return false;
        }

        $customer = $message->getTicket()->getCustomer();
        $locale = $customer->getLocale();

        $subject = $this->translator->trans('ekyna_commerce.ticket_message.notify.customer.subject', [
            '%site_name%' => $this->settingsManager->getParameter('general.site_name'),
        ], null, $locale);

        $body = $this->templating->render('@EkynaCommerce/Email/customer_ticket_message.html.twig', [
            'subject' => $subject,
            'message' => $message,
            'locale'  => $locale,
        ]);

        $to = [$customer->getEmail() => $customer->getFirstName() . ' ' . $customer->getLastName()];

        return 0 < $this->mailer->send($this->createMessage($subject, $body, $to, false));
    }

    /**
     * Notifies the administrator about the ticket message creation or update.
     *
     * @param TicketMessageInterface[] $messages
     * @param UserInterface            $admin
     *
     * @return bool
     */
    public function sendTicketMessagesToAdmin(array $messages, UserInterface $admin): bool
    {
        foreach ($messages as $message) {
            if (!$message instanceof TicketMessageInterface) {
                throw new UnexpectedValueException("Expected instance of " . TicketMessageInterface::class);
            }
            if (!$message->isCustomer()) {
                throw new LogicException("Expected customer messages.");
            }
        }

        $subject = $this->translator->trans('ekyna_commerce.ticket_message.notify.admin.subject');

        $body = $this->templating->render('@EkynaCommerce/Email/admin_ticket_message.html.twig', [
            'subject'  => $subject,
            'messages' => $messages,
        ]);

        $to = [$admin->getEmail() => $admin->hasFullName() ? $admin->getFullName() : null];

        return 0 < $this->mailer->send($this->createMessage($subject, $body, $to, false));
    }

    /**
     * Notifies the customer about the generated coupons.
     *
     * @param CustomerInterface $customer
     * @param CouponInterface[] $coupons
     *
     * @return bool
     */
    public function sendCustomerCoupons(CustomerInterface $customer, array $coupons): bool
    {
        foreach ($coupons as $coupon) {
            if (!$coupon instanceof CouponInterface) {
                throw new UnexpectedValueException("Expected instance of " . CouponInterface::class);
            }
            if ($customer !== $coupon->getCustomer()) {
                throw new LogicException("Unexpected coupon owner");
            }
        }

        $locale = $customer->getLocale();

        $subject = $this->translator->trans('ekyna_commerce.coupon.notify.customer.subject', [
            '%site_name%' => $this->settingsManager->getParameter('general.site_name'),
        ], null, $locale);

        $body = $this->templating->render('@EkynaCommerce/Email/customer_coupons.html.twig', [
            'subject' => $subject,
            'coupons' => $coupons,
            'locale'  => $locale,
        ]);

        $to = [$customer->getEmail() => $customer->getFirstName() . ' ' . $customer->getLastName()];

        return 0 < $this->mailer->send($this->createMessage($subject, $body, $to, false));
    }

    /**
     * Sends the notification message.
     *
     * @param Notify $notify
     *
     * @return bool
     */
    public function sendNotify(Notify $notify): bool
    {
        if ($notify->isEmpty()) {
            return false;
        }

        $message = new \Swift_Message();
        $report = '';
        $attachments = [];

        // FROM
        $from = $notify->getFrom();
        $message->setFrom($from->getEmail(), $from->getName());
        $message->setReplyTo($from->getEmail(), $from->getName());

        $report .= "From: {$this->formatRecipient($from)}\n";
        $report .= "Reply-To: {$this->formatRecipient($from)}\n";

        // TO
        foreach ($notify->getRecipients() as $recipient) {
            $message->addTo($recipient->getEmail(), $recipient->getName());
            $report .= "To: {$this->formatRecipient($recipient)}\n";
        }
        foreach ($notify->getExtraRecipients() as $recipient) {
            $message->addTo($recipient->getEmail(), $recipient->getName());
            $report .= "To: {$this->formatRecipient($recipient)}\n";
        }

        // Copy
        foreach ($notify->getCopies() as $recipient) {
            $message->addCc($recipient->getEmail(), $recipient->getName());
            $report .= "Cc: {$this->formatRecipient($recipient)}\n";
        }
        foreach ($notify->getExtraCopies() as $recipient) {
            $message->addCc($recipient->getEmail(), $recipient->getName());
            $report .= "Cc: {$this->formatRecipient($recipient)}\n";
        }

        // Invoices
        foreach ($notify->getInvoices() as $invoice) {
            $renderer = $this->rendererFactory->createRenderer($invoice);
            try {
                $content = $renderer->render(RendererInterface::FORMAT_PDF);
            } catch (PdfException $e) {
                $notify->setError(true);
                $report .= "ERROR: failed to generate PDF for invoice {$invoice->getNumber()}\n";
                continue;
            }

            $filename = $renderer->getFilename() . '.pdf';

            $message->attach(new \Swift_Attachment($content, $filename, 'application/pdf'));

            $attachments[$filename] = $this->translator->trans('ekyna_commerce.invoice.label.singular');
            $report .= "Attachment: $filename\n";
        }

        // Shipments
        foreach ($notify->getShipments() as $shipment) {
            $renderer = $this->rendererFactory->createRenderer($shipment, CDocumentTypes::TYPE_SHIPMENT_BILL);
            try {
                $content = $renderer->render(RendererInterface::FORMAT_PDF);
            } catch (PdfException $e) {
                $notify->setError(true);
                $report .= "ERROR: failed to generate PDF for shipment {$shipment->getNumber()}\n";
                continue;
            }

            $filename = $renderer->getFilename() . '.pdf';

            $message->attach(new \Swift_Attachment($content, $filename, 'application/pdf'));

            $attachments[$filename] = $this->translator->trans(
                'ekyna_commerce.document.type.' . ($shipment->isReturn() ? 'return' : 'shipment') . '_bill'
            );
            $report .= "Attachment: $filename\n";
        }

        // Labels
        if (0 < $notify->getLabels()->count()) {
            $content = null;
            try {
                $content = $this->shipmentLabelRenderer->render($notify->getLabels(), true);
            } catch (PdfException $e) {
                $notify->setError(true);
                $report .= "ERROR: failed to generate PDF for labels\n";
            }

            if (!empty($content)) {
                $filename = 'labels.pdf';

                $message->attach(new \Swift_Attachment($content, $filename, 'application/pdf'));

                $attachments[$filename] = $this->translator->trans(
                    'ekyna_commerce.shipment_label.label.' . (1 < $notify->getLabels()->count() ? 'plural' : 'singular')
                );
                $report .= "Attachment: $filename\n";
            }
        }

        $source = $notify->getSource();

        // Attachments
        foreach ($notify->getAttachments() as $attachment) {
            if (!$this->filesystem->has($path = $attachment->getPath())) {
                throw new RuntimeException("Attachment file '$path' not found.");
            }

            /** @var \League\Flysystem\File $file */
            $file = $this->filesystem->get($path);
            $filename = pathinfo($path, PATHINFO_BASENAME);

            $message->attach(new \Swift_Attachment($file->read(), $filename, $file->getMimetype()));

            if (!empty($type = $attachment->getType())) {
                if ($source instanceof SupplierOrderInterface) {
                    $attachments[$filename] = $this->translator->trans(SupplierOrderAttachmentTypes::getLabel($type));
                } else {
                    $attachments[$filename] = $this->translator->trans(BDocumentTypes::getLabel($type));
                }
            } else {
                $attachments[$filename] = $attachment->getTitle();
            }
            $report .= "Attachment: $filename\n";
        }

        // SUBJECT
        $message->setSubject($notify->getSubject());
        $report .= "Subject: {$notify->getSubject()}\n";

        // CONTENT
        $content = null;
        if ($source instanceof SupplierOrderInterface) {
            // Supplier order form
            if ($notify->isIncludeForm()) {
                $renderer = $this->rendererFactory->createRenderer($source);
                $content = null;
                try {
                    $content = $renderer->render(RendererInterface::FORMAT_PDF);
                } catch (PdfException $e) {
                    $notify->setError(true);
                    $report .= "ERROR: failed to generate PDF for supplier order {$source->getNumber()}\n";
                }

                if ($content) {
                    $filename = $renderer->getFilename() . '.pdf';

                    $message->attach(new \Swift_Attachment($content, $filename, 'application/pdf'));

                    $attachments[$filename] = $this->translator->trans('ekyna_commerce.document.type.form');
                    $report .= "Attachment: $filename\n";
                }
            }

            try {
                $content = $this->templating->render('@EkynaCommerce/Email/supplier_order_notify.html.twig', [
                    'notify'      => $notify,
                    'order'       => $source,
                    'attachments' => $attachments,
                ]);
            } catch (\RuntimeException $e) {
                $notify->setError(true);
                $report .= "ERROR: failed to generate HTML message for supplier order {$source->getNumber()}\n";
            }
        } else {
            $sale = $this->getNotifySale($notify);
            try {
                $content = $this->templating->render('@EkynaCommerce/Email/sale_notify.html.twig', [
                    'notify'      => $notify,
                    'sale'        => $sale,
                    'attachments' => $attachments,
                ]);
            } catch (\RuntimeException $e) {
                $notify->setError(true);
                $report .= "ERROR: failed to generate HTML message for sale {$sale->getNumber()}\n";
            }
        }
        $message->setBody($content, 'text/html');

        if (!empty($notify->getCustomMessage())) {
            $report .= "Message: {$notify->getCustomMessage()}\n";
        }

        $notify->setReport($report);

        // Don't send if it has error(s)
        if ($notify->isError()) {
            return 0;
        }

        // Trigger IMAP copy
        if (!$notify->isTest()) {
            $message->getHeaders()->addTextHeader(ImapCopyPlugin::HEADER, 'do');
        }

        return 0 < $this->mailer->send($message);
    }

    /**
     * Sends the notify failure report.
     *
     * @param Notify $notify
     */
    public function sendNotifyFailure(Notify $notify): void
    {
        $message = new \Swift_Message();

        $message
            ->setSubject("Notification failed")
            ->setBody("Notification failure\n\n" . $notify->getReport(), 'text/plain')
            ->setFrom(
                $this->settingsManager->getParameter('notification.from_email'),
                $this->settingsManager->getParameter('notification.from_name')
            )
            ->setTo($notify->getFrom()->getEmail(), $notify->getFrom()->getName());

       $this->mailer->send($message);
    }

    /**
     * Sends the email.
     *
     * @param string            $subject
     * @param string            $body
     * @param string|array      $to
     * @param string|array|bool $from
     *
     * @return \Swift_Message
     */
    protected function createMessage(
        string $subject,
        string $body,
        $to = null,
        $from = null
    ): \Swift_Message {
        if (empty($to)) {
            $to = $this->settingsManager->getParameter('notification.to_emails');
        }

        if (false === $from) {
            $from = $this->settingsManager->getParameter('notification.no_reply');
        }

        if (empty($from)) {
            $from = [
                $this->settingsManager->getParameter('notification.from_email') =>
                    $this->settingsManager->getParameter('notification.from_name')
            ];
        }

        $message = new \Swift_Message();

        return $message
            ->setSubject($subject)
            ->setBody($body, 'text/html')
            ->setTo($to)
            ->setFrom($from);
    }

    /**
     * Formats the recipient.
     *
     * @param Recipient $recipient
     *
     * @return string
     */
    private function formatRecipient(Recipient $recipient)
    {
        if (empty($recipient->getName())) {
            return $recipient->getEmail();
        }

        return sprintf('%s <%s>', $recipient->getName(), $recipient->getEmail());
    }

    /**
     * Returns the sale from the notify object.
     *
     * @param Notify $notify
     *
     * @return SaleInterface
     */
    private function getNotifySale(Notify $notify)
    {
        $source = $notify->getSource();

        if ($source instanceof SaleInterface) {
            return $source;
        } elseif (
            $source instanceof PaymentInterface ||
            $source instanceof ShipmentInterface ||
            $source instanceof InvoiceInterface
        ) {
            return $source->getSale();
        }

        throw new RuntimeException("Failed to fetch the sale from the notify object.");
    }
}
