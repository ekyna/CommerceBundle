<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Mailer;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes as BDocumentTypes;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderAttachmentTypes;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderSubmit;
use Ekyna\Bundle\CommerceBundle\Model\TicketMessageInterface;
use Ekyna\Component\Commerce\Common\Model\CouponInterface;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Ekyna\Component\Commerce\Common\Model\Recipient;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

use function file_get_contents;
use function is_resource;
use function sprintf;
use function stream_get_contents;
use function strpos;
use function substr;

/**
 * Class Mailer
 * @package Ekyna\Bundle\CommerceBundle\Service\Mailer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Mailer
{
    public function __construct(
        private readonly MailerInterface     $mailer,
        private readonly Environment         $twig,
        private readonly TranslatorInterface $translator,
        private readonly AddressHelper       $addressHelper,
        private readonly AttachmentHelper    $attachmentHelper,
    ) {
    }

    /**
     * Notifies the administrator about a potential fraudster.
     */
    public function sendAdminFraudsterAlert(CustomerInterface $customer): bool
    {
        $body = $this->twig->render('@EkynaCommerce/Email/admin_fraudster_alert.html.twig', [
            'customer' => $customer,
        ]);

        $subject = $this->translator->trans('customer.notify.fraudster.subject', [], 'EkynaCommerce');

        return $this->send($this->createMessage($subject, $body));
    }

    /**
     * Notifies the administrator about a customer registration.
     */
    public function sendAdminCustomerRegistration(CustomerInterface $customer): bool
    {
        $body = $this->twig->render('@EkynaCommerce/Email/admin_customer_registration.html.twig', [
            'customer' => $customer,
        ]);

        $subject = $this->translator->trans('customer.notify.registration.subject', [], 'EkynaCommerce');

        return $this->send($this->createMessage($subject, $body));
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

        $body = $this->twig->render('@EkynaCommerce/Email/customer_balance.html.twig', [
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

        return $this->send($message);
    }

    /**
     * Sends the supplier order submit message.
     */
    public function sendSupplierOrderSubmit(SupplierOrderSubmit $submit): bool
    {
        $order = $submit->getOrder();

        $sender = $this->addressHelper->getPurchaseAddress();

        $message = $this->createMessage(
            $submit->getSubject(),
            $submit->getMessage(),
            $submit->getEmails(),
            $sender
        );

        $user = $this->addressHelper->getAdminHelper()->getCurrentUserSender();
        if ($user && ($user->getAddress() !== $sender->getAddress())) {
            $message->addCc($user);
        }

        // Form attachment
        $this
            ->attachmentHelper
            ->attachSupplierOrder($message, $order, $order->getNumber() . '.pdf');

        // Subjects labels
        if ($submit->isSendLabels()) {
            $this
                ->attachmentHelper
                ->attachSupplierOrderSubjectLabels($message, $order);
        }

        return $this->send($message);
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
            '%site_name%' => $this->addressHelper->getAdminHelper()->getSiteName(),
        ], 'EkynaCommerce', $locale);

        $body = $this->twig->render('@EkynaCommerce/Email/customer_ticket_message.html.twig', [
            'subject' => $subject,
            'message' => $message,
            'locale'  => $locale,
        ]);

        $to = new Address($customer->getEmail(), trim($customer->getFirstName() . ' ' . $customer->getLastName()));

        return $this->send($this->createMessage($subject, $body, $to, false));
    }

    /**
     * Notifies the administrator about the ticket message creation or update.
     *
     * @param TicketMessageInterface[] $messages
     */
    public function sendTicketMessagesToAdmin(array $messages, ?UserInterface $admin): bool
    {
        foreach ($messages as $message) {
            if (!$message instanceof TicketMessageInterface) {
                throw new UnexpectedTypeException($message, TicketMessageInterface::class);
            }

            if (!$message->isCustomer()) {
                throw new LogicException('Expected customer messages.');
            }
        }

        $type = $admin ? 'admin' : 'unassigned';
        $subject = $this->translator->trans("ticket_message.notify.$type.subject", [], 'EkynaCommerce');
        $content = $this->translator->trans("ticket_message.notify.$type.content", [], 'EkynaCommerce');

        $body = $this->twig->render('@EkynaCommerce/Email/admin_ticket_message.html.twig', [
            'subject'  => $subject,
            'content'  => $content,
            'messages' => $messages,
        ]);

        $to = $admin ? new Address($admin->getEmail(), $admin->hasFullName() ? $admin->getFullName() : '') : null;

        return $this->send($this->createMessage($subject, $body, $to, false));
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
            '%site_name%' => $this->addressHelper->getAdminHelper()->getSiteName(),
        ], 'EkynaCommerce', $locale);

        $body = $this->twig->render('@EkynaCommerce/Email/customer_coupons.html.twig', [
            'subject' => $subject,
            'coupons' => $coupons,
            'locale'  => $locale,
        ]);

        $to = new Address($customer->getEmail(), trim($customer->getFirstName() . ' ' . $customer->getLastName()));

        return $this->send($this->createMessage($subject, $body, $to, false));
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
            try {
                $filename = $this
                    ->attachmentHelper
                    ->attachInvoice($message, $invoice);
            } catch (RuntimeException) {
                $notify->setError(true);
                $report .= "ERROR: failed to generate PDF for invoice {$invoice->getNumber()}\n";
                continue;
            }

            $attachments[$filename] = $this->translator->trans('invoice.label.singular', [], 'EkynaCommerce');
            $report .= "Attachment: $filename\n";
        }

        // Shipments
        foreach ($notify->getShipments() as $shipment) {
            try {
                $filename = $this
                    ->attachmentHelper
                    ->attachShipment($message, $shipment);
            } catch (RuntimeException) {
                $notify->setError(true);
                $report .= "ERROR: failed to generate PDF for shipment {$shipment->getNumber()}\n";
                continue;
            }

            $attachments[$filename] = $this->translator->trans(
                'document.type.' . ($shipment->isReturn() ? 'return' : 'shipment') . '_bill',
                [],
                'EkynaCommerce'
            );
            $report .= "Attachment: $filename\n";
        }

        // Labels
        if (0 < $notify->getLabels()->count()) {
            $count = 0;
            foreach ($notify->getLabels() as $label) {
                // TODO Use commerce mailer helper
                $count++;

                $format = $label->getFormat();

                $filename = sprintf('label-%d.%s', $count, substr($format, strpos($format, '/') + 1));

                if (is_resource($content = $label->getContent())) {
                    $content = stream_get_contents($content);
                }

                $message->attach($content, $filename, $format);

                $attachments[$filename] = $filename;

                $report .= "Attachment: $filename\n";
            }
        }

        $source = $notify->getSource();

        // Attachments
        foreach ($notify->getAttachments() as $attachment) {
            try {
                $filename = $this
                    ->attachmentHelper
                    ->attach($message, $attachment);
            } catch (RuntimeException) {
                $notify->setError(true);
                $report .= "ERROR: failed to attach {$attachment->getPath()}\n";
                continue;
            }

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
                try {
                    $filename = $this
                        ->attachmentHelper
                        ->attachSupplierOrder($message, $source);

                    $attachments[$filename] = $this->translator->trans('document.type.form', [], 'EkynaCommerce');
                    $report .= "Attachment: $filename\n";
                } catch (RuntimeException) {
                    $notify->setError(true);
                    $report .= "ERROR: failed to generate PDF for supplier order {$source->getNumber()}\n";
                }
            }

            try {
                $content = $this->twig->render('@EkynaCommerce/Email/supplier_order_notify.html.twig', [
                    'notify'      => $notify,
                    'order'       => $source,
                    'attachments' => $attachments,
                ]);
            } catch (\RuntimeException) {
                $notify->setError(true);
                $report .= "ERROR: failed to generate HTML message for supplier order {$source->getNumber()}\n";
            }
        } else {
            $sale = $this->getNotifySale($notify);
            try {
                $content = $this->twig->render('@EkynaCommerce/Email/sale_notify.html.twig', [
                    'notify'      => $notify,
                    'sale'        => $sale,
                    'attachments' => $attachments,
                ]);
            } catch (\RuntimeException) {
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

        return $this->send($message);
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
            ->from($this->addressHelper->getAdminHelper()->getNotificationSender())
            ->to(new Address($notify->getFrom()->getEmail(), $notify->getFrom()->getName()));

        $this->send($message);
    }

    /**
     * Sends the email.
     */
    protected function createMessage(
        string                    $subject,
        string                    $body,
        Address|string|array      $to = null,
        Address|string|array|bool $from = null
    ): Email {
        $helper = $this->addressHelper->getAdminHelper();

        if (empty($to)) {
            $to = $helper->getNotificationRecipients();
        }

        if (false === $from) {
            $from = $helper->getNoReply();
        }

        if (empty($from)) {
            $from = $helper->getNotificationSender();
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

    private function send(Email $email): bool
    {
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface) {
            return false;
        }

        return true;
    }
}
