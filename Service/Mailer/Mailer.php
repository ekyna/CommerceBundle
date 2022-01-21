<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Mailer;

use DateTime;
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
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Bundle\SettingBundle\Manager\SettingManagerInterface;
use Ekyna\Component\Commerce\Common\Model\CouponInterface;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Ekyna\Component\Commerce\Common\Model\Recipient;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes as CDocumentTypes;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Resource\Exception\PdfException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Class Mailer
 * @package Ekyna\Bundle\CommerceBundle\Service\Mailer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Mailer
{
    protected MailerFactory           $mailer;
    protected Environment             $templating;
    protected TranslatorInterface     $translator;
    protected SettingManagerInterface $settingsManager;
    protected RendererFactory         $rendererFactory;
    protected ShipmentLabelRenderer   $shipmentLabelRenderer;
    protected SubjectLabelRenderer    $subjectLabelRenderer;
    protected SubjectHelperInterface  $subjectHelper;
    protected FilesystemOperator      $filesystem;

    private ?Address $notificationSender = null;

    public function __construct(
        MailerFactory $mailer,
        Environment $templating,
        TranslatorInterface $translator,
        SettingManagerInterface $settingsManager,
        RendererFactory $rendererFactory,
        ShipmentLabelRenderer $shipmentLabelRenderer,
        SubjectLabelRenderer $subjectLabelRenderer,
        SubjectHelperInterface $subjectHelper,
        FilesystemOperator $filesystem
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
     */
    public function sendAdminFraudsterAlert(CustomerInterface $customer): bool
    {
        $body = $this->templating->render('@EkynaCommerce/Email/admin_fraudster_alert.html.twig', [
            'customer' => $customer,
        ]);

        $subject = $this->translator->trans('customer.notify.fraudster.subject', [], 'EkynaCommerce');

        return $this->mailer->send($this->createMessage($subject, $body));
    }

    /**
     * Notifies the administrator about a customer registration.
     */
    public function sendAdminCustomerRegistration(CustomerInterface $customer): bool
    {
        $body = $this->templating->render('@EkynaCommerce/Email/admin_customer_registration.html.twig', [
            'customer' => $customer,
        ]);

        $subject = $this->translator->trans('customer.notify.registration.subject', [], 'EkynaCommerce');

        return $this->mailer->send($this->createMessage($subject, $body));
    }

    public function sendCustomerRegistrationConfirmation(CustomerInterface $customer): void
    {
        // TODO
    }

    /**
     * Notifies the customer about his account balance.
     */
    public function sendCustomerBalance(CustomerInterface $customer, array $balance, string $csvPath = null): bool
    {
        if (empty($balance)) {
            return false;
        }

        $locale = $customer->getLocale();

        $subject = $this
            ->translator
            ->trans('notify.type.balance.subject', [], 'EkynaCommerce', $locale);

        $body = $this->templating->render('@EkynaCommerce/Email/customer_balance.html.twig', [
            'subject'  => $subject,
            'locale'   => $locale,
            'customer' => $customer,
            'balance'  => $balance,
        ]);

        $to = new Address($customer->getEmail(), trim($customer->getFirstName() . ' ' . $customer->getLastName()));

        $message = $this->createMessage($subject, $body, $to);

        // CSV attachment if any
        if (!empty($csvPath) && is_file($csvPath)) {
            $message->attach(file_get_contents($csvPath), 'account-balance.csv', 'text/csv');
        }

        // Trigger IMAP copy
        // TODO $message->getHeaders()->addTextHeader(ImapCopyPlugin::HEADER, 'do');

        return $this->mailer->send($message);
    }

    /**
     * Sends the supplier order submit message.
     */
    public function sendSupplierOrderSubmit(SupplierOrderSubmit $submit): bool
    {
        $order = $submit->getOrder();

        $message = $this->createMessage($submit->getSubject(), $submit->getMessage(), $submit->getEmails());

        // Form attachment
        $renderer = $this->rendererFactory->createRenderer($order);
        $message->attach(
            $renderer->render(RendererInterface::FORMAT_PDF),
            $order->getNumber() . '.pdf',
            'application/pdf'
        );

        // Subjects labels
        if ($submit->isSendLabels()) {
            $subjects = [];
            foreach ($order->getItems() as $item) {
                $subjects[] = $this->subjectHelper->resolve($item);
            }

            $labels = $this->subjectLabelRenderer->buildLabels($subjects);

            $date = $order->getOrderedAt() ?? new DateTime();
            $extra = sprintf('%s (%s)', $order->getNumber(), $date->format('Y-m-d'));
            foreach ($labels as $label) {
                $label->setExtra($extra);
            }

            $message->attach(
                $this->subjectLabelRenderer->render($labels),
                'labels.pdf',
                'application/pdf'
            );
        }

        // Trigger imap copy
        // TODO $message->getHeaders()->addTextHeader(ImapCopyPlugin::HEADER, 'do');

        return $this->mailer->send($message);
    }

    /**
     * Notifies the customer about the ticket message creation or update.
     */
    public function sendTicketMessageToCustomer(TicketMessageInterface $message): bool
    {
        if ($message->isCustomer()) {
            throw new LogicException('Expected admin message.');
        }
        if ($message->isInternal() || $message->getTicket()->isInternal()) {
            throw new LogicException('Unexpected internal message.');
        }

        if (!$message->isNotify()) {
            return false;
        }

        $customer = $message->getTicket()->getCustomer();
        $locale = $customer->getLocale();

        $subject = $this->translator->trans('ticket_message.notify.customer.subject', [
            '%site_name%' => $this->settingsManager->getParameter('general.site_name'),
        ], 'EkynaCommerce', $locale);

        $body = $this->templating->render('@EkynaCommerce/Email/customer_ticket_message.html.twig', [
            'subject' => $subject,
            'message' => $message,
            'locale'  => $locale,
        ]);

        $to = new Address($customer->getEmail(), trim($customer->getFirstName() . ' ' . $customer->getLastName()));

        return $this->mailer->send($this->createMessage($subject, $body, $to, false));
    }

    /**
     * Notifies the administrator about the ticket message creation or update.
     *
     * @param TicketMessageInterface[] $messages
     */
    public function sendTicketMessagesToAdmin(array $messages, UserInterface $admin): bool
    {
        foreach ($messages as $message) {
            if (!$message instanceof TicketMessageInterface) {
                throw new UnexpectedTypeException($message, TicketMessageInterface::class);
            }

            if (!$message->isCustomer()) {
                throw new LogicException('Expected customer messages.');
            }
        }

        $subject = $this->translator->trans('ticket_message.notify.admin.subject', [], 'EkynaCommerce');

        $body = $this->templating->render('@EkynaCommerce/Email/admin_ticket_message.html.twig', [
            'subject'  => $subject,
            'messages' => $messages,
        ]);

        $to = new Address($admin->getEmail(), $admin->hasFullName() ? $admin->getFullName() : '');

        return $this->mailer->send($this->createMessage($subject, $body, $to, false));
    }

    /**
     * Notifies the customer about the generated coupons.
     *
     * @param CouponInterface[] $coupons
     */
    public function sendCustomerCoupons(CustomerInterface $customer, array $coupons): bool
    {
        foreach ($coupons as $coupon) {
            if (!$coupon instanceof CouponInterface) {
                throw new UnexpectedTypeException($coupon, CouponInterface::class);
            }
            if ($customer !== $coupon->getCustomer()) {
                throw new LogicException('Unexpected coupon owner');
            }
        }

        $locale = $customer->getLocale();

        $subject = $this->translator->trans('coupon.notify.customer.subject', [
            '%site_name%' => $this->settingsManager->getParameter('general.site_name'),
        ], 'EkynaCommerce', $locale);

        $body = $this->templating->render('@EkynaCommerce/Email/customer_coupons.html.twig', [
            'subject' => $subject,
            'coupons' => $coupons,
            'locale'  => $locale,
        ]);

        $to = new Address($customer->getEmail(), trim($customer->getFirstName() . ' ' . $customer->getLastName()));

        return $this->mailer->send($this->createMessage($subject, $body, $to, false));
    }

    /**
     * Sends the notification message.
     */
    public function sendNotify(Notify $notify): bool
    {
        if ($notify->isEmpty()) {
            return false;
        }

        $message = new Email();
        $report = '';
        $attachments = [];


        // FROM
        $from = $notify->getFrom();
        $recipientAddress = new Address($from->getEmail(), $from->getName());

        $message->from($recipientAddress);
        $message->replyTo($recipientAddress);

        $report .= "From: {$this->formatRecipient($from)}\n";
        $report .= "Reply-To: {$this->formatRecipient($from)}\n";

        // TO
        foreach ($notify->getRecipients() as $recipient) {
            $message->addTo($this->recipientToAddress($recipient));
            $report .= "To: {$this->formatRecipient($recipient)}\n";
        }
        foreach ($notify->getExtraRecipients() as $recipient) {
            $message->addTo($this->recipientToAddress($recipient));
            $report .= "To: {$this->formatRecipient($recipient)}\n";
        }

        // Copy
        foreach ($notify->getCopies() as $recipient) {
            $message->addCc($this->recipientToAddress($recipient));
            $report .= "Cc: {$this->formatRecipient($recipient)}\n";
        }
        foreach ($notify->getExtraCopies() as $recipient) {
            $message->addCc($this->recipientToAddress($recipient));
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

            $message->attach($content, $filename, 'application/pdf');

            $attachments[$filename] = $this->translator->trans('invoice.label.singular', [], 'EkynaCommerce');
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

            $message->attach($content, $filename, 'application/pdf');

            $attachments[$filename] = $this->translator->trans(
                'document.type.' . ($shipment->isReturn() ? 'return' : 'shipment') . '_bill',
                [], 'EkynaCommerce'
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

                $message->attach($content, $filename, 'application/pdf');

                $attachments[$filename] = $this->translator->trans(
                    'shipment_label.label.' . (1 < $notify->getLabels()->count() ? 'plural' : 'singular'),
                    [], 'EkynaCommerce'
                );
                $report .= "Attachment: $filename\n";
            }
        }

        $source = $notify->getSource();

        // Attachments
        foreach ($notify->getAttachments() as $attachment) {
            if (!$this->filesystem->fileExists($path = $attachment->getPath())) {
                throw new RuntimeException("Attachment file '$path' not found.");
            }

            $content = $this->filesystem->readStream($path);
            $filename = pathinfo($path, PATHINFO_BASENAME);
            $mimeType = $this->filesystem->mimeType($path);

            $message->attach($content, $filename, $mimeType);

            if (!empty($type = $attachment->getType())) {
                if ($source instanceof SupplierOrderInterface) {
                    $attachments[$filename] = SupplierOrderAttachmentTypes::getLabel($type)->trans($this->translator);
                } else {
                    $attachments[$filename] = BDocumentTypes::getLabel($type)->trans($this->translator);
                }
            } else {
                $attachments[$filename] = $attachment->getTitle();
            }
            $report .= "Attachment: $filename\n";
        }

        // SUBJECT
        $message->subject($notify->getSubject());
        $report .= "Subject: {$notify->getSubject()}\n";

        // CONTENT
        $content = null;
        if ($source instanceof SupplierOrderInterface) {
            // Supplier order form
            if ($notify->isIncludeForm()) {
                $renderer = $this->rendererFactory->createRenderer($source);
                try {
                    $content = $renderer->render(RendererInterface::FORMAT_PDF);
                } catch (PdfException $e) {
                    $notify->setError(true);
                    $report .= "ERROR: failed to generate PDF for supplier order {$source->getNumber()}\n";
                }

                if ($content) {
                    $filename = $renderer->getFilename() . '.pdf';

                    $message->attach($content, $filename, 'application/pdf');

                    $attachments[$filename] = $this->translator->trans('document.type.form', [], 'EkynaCommerce');
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

        $message->html($content);

        if (!empty($notify->getCustomMessage())) {
            $report .= "Message: {$notify->getCustomMessage()}\n";
        }

        $notify->setReport($report);

        // Don't send if it has error(s)
        if ($notify->isError()) {
            return false;
        }

        // Trigger IMAP copy
        if (!$notify->isTest()) {
            // TODO $message->getHeaders()->addTextHeader(ImapCopyPlugin::HEADER, 'do');
        }

        return $this->mailer->send($message);
    }

    /**
     * Sends notify failure report.
     */
    public function sendNotifyFailure(Notify $notify): void
    {
        $message = new Email();

        $message
            ->subject('Notification failed')
            ->text("Notification failure\n\n" . $notify->getReport())
            ->from($this->getNotificationSenderAddress())
            ->to(new Address($notify->getFrom()->getEmail(), $notify->getFrom()->getName()));

        $this->mailer->getDefaultMailer()->send($message);
    }

    /**
     * Sends the email.
     *
     * @param string|array      $to
     * @param string|array|bool $from
     */
    protected function createMessage(
        string $subject,
        string $body,
        $to = null,
        $from = null
    ): Email {
        if (empty($to)) {
            $to = $this->settingsManager->getParameter('notification.to_emails');
        }

        if (false === $from) {
            $from = $this->settingsManager->getParameter('notification.no_reply');
        }

        if (empty($from)) {
            $from = $this->getNotificationSenderAddress();
        }

        if (!is_array($to)) {
            $to = [$to];
        }
        if (!is_array($from)) {
            $from = [$from];
        }

        $message = new Email();

        return $message
            ->subject($subject)
            ->html($body)
            ->to(...$to)
            ->from(...$from);
    }

    private function formatRecipient(Recipient $recipient): string
    {
        if (empty($recipient->getName())) {
            return $recipient->getEmail();
        }

        return sprintf('%s <%s>', $recipient->getName(), $recipient->getEmail());
    }

    private function recipientToAddress(Recipient $recipient): Address
    {
        return new Address($recipient->getEmail(), $recipient->getName());
    }

    private function getNotificationSenderAddress(): Address
    {
        if (null !== $this->notificationSender) {
            return $this->notificationSender;
        }

        return $this->notificationSender = new Address(
            $this->settingsManager->getParameter('notification.from_email'),
            $this->settingsManager->getParameter('notification.from_name')
        );
    }

    /**
     * Returns the sale from notify object.
     */
    private function getNotifySale(Notify $notify): SaleInterface
    {
        $source = $notify->getSource();

        if ($source instanceof SaleInterface) {
            return $source;
        } elseif (
            $source instanceof PaymentInterface
            || $source instanceof ShipmentInterface
            || $source instanceof InvoiceInterface
        ) {
            return $source->getSale();
        }

        throw new RuntimeException('Failed to fetch the sale from the notify object.');
    }
}
