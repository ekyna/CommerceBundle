<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Mailer;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\AdminBundle\Service\Mailer\MailerFactory;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderSubmit;
use Ekyna\Bundle\CommerceBundle\Model\TicketMessageInterface;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererInterface;
use Ekyna\Bundle\CommerceBundle\Service\Document\ShipmentRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\LabelRenderer as ShipmentLabelRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Subject\LabelRenderer as SubjectLabelRenderer;
use Ekyna\Bundle\CoreBundle\Service\SwiftMailer\ImapCopyPlugin;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Ekyna\Component\Commerce\Common\Model\Recipient;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
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
     * @return integer
     */
    public function sendAdminFraudsterAlert(CustomerInterface $customer)
    {
        $body = $this->templating->render('@EkynaCommerce/Email/admin_fraudster_alert.html.twig', [
            'customer' => $customer,
        ]);

        $subject = $this->translator->trans('ekyna_commerce.customer.notify.fraudster.subject');

        return $this->sendEmail($body, $subject);
    }

    /**
     * Sends the email.
     *
     * @param string $body
     * @param string $toEmail
     * @param string $subject
     *
     * @return int
     */
    protected function sendEmail($body, $subject, $toEmail = null)
    {
        $fromEmail = $this->settingsManager->getParameter('notification.from_email');
        $fromName = $this->settingsManager->getParameter('notification.from_name');

        if (null === $toEmail) {
            $toEmail = $this->settingsManager->getParameter('notification.to_emails');
        }

        $message = new \Swift_Message();
        $message
            ->setSubject($subject)
            ->setFrom($fromEmail, $fromName)
            ->setTo($toEmail)
            ->setBody($body, 'text/html');

        return $this->mailer->send($message);
    }

    /**
     * Notifies the administrator about a customer registration.
     *
     * @param CustomerInterface $customer
     *
     * @return integer
     */
    public function sendAdminCustomerRegistration(CustomerInterface $customer)
    {
        $body = $this->templating->render('@EkynaCommerce/Email/admin_customer_registration.html.twig', [
            'customer' => $customer,
        ]);

        $subject = $this->translator->trans('ekyna_commerce.customer.notify.registration.subject');

        return $this->sendEmail($body, $subject);
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
    public function sendCustomerBalance(CustomerInterface $customer, array $balance, string $csvPath = null)
    {
        if (empty($balance)) {
            return false;
        }

        $locale = $customer->getLocale();

        $fromEmail = $this->settingsManager->getParameter('notification.from_email');
        $fromName = $this->settingsManager->getParameter('notification.from_name');

        $subject = $this
            ->translator
            ->trans('ekyna_commerce.notify.type.balance.subject', [], null, $locale);

        $body = $this->templating->render('@EkynaCommerce/Email/customer_balance.html.twig', [
            'subject'  => $subject,
            'locale'   => $locale,
            'customer' => $customer,
            'balance'  => $balance,
        ]);

        $message = new \Swift_Message();
        $message
            ->setSubject($subject)
            ->setFrom($fromEmail, $fromName)
            ->setTo($customer->getEmail())
            ->setBody($body, 'text/html');

        // CSV attachment if any
        if (!empty($csvPath) && is_file($csvPath)) {
            $message->attach(
                \Swift_Attachment::newInstance(file_get_contents($csvPath), 'account-balance.csv', 'text/csv')
            );
        }

        // Trigger imap copy
        $message->getHeaders()->addTextHeader(ImapCopyPlugin::HEADER, 'do');

        return 0 < $this->mailer->send($message);
    }

    /**
     * Sends the supplier order submit message.
     *
     * @param SupplierOrderSubmit $submit
     *
     * @return bool
     */
    public function sendSupplierOrderSubmit(SupplierOrderSubmit $submit)
    {
        $order = $submit->getOrder();

        $fromEmail = $this->settingsManager->getParameter('notification.from_email');
        $fromName = $this->settingsManager->getParameter('notification.from_name');

        $subject = $this
            ->translator
            ->trans('ekyna_commerce.supplier_order_attachment.type.form', [], null, $order->getLocale());
        $subject .= ' ' . $order->getNumber();

        $message = new \Swift_Message();
        $message
            ->setSubject($subject)
            ->setFrom($fromEmail, $fromName)
            ->setTo($submit->getEmails())
            ->setBody($submit->getMessage(), 'text/html');

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
     * @return int
     */
    public function sendTicketMessageToCustomer(TicketMessageInterface $message)
    {
        if ($message->isCustomer()) {
            throw new LogicException("Expected admin message.");
        }

        if (!$message->isNotify()) {
            return 0;
        }

        if (empty($fromEmail = $this->settingsManager->getParameter('notification.no_reply'))) {
            $fromEmail = $this->settingsManager->getParameter('notification.from_email');
        }
        $fromName = $this->settingsManager->getParameter('notification.from_name');

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

        $email = new \Swift_Message();
        $email
            ->setSubject($subject)
            ->setFrom($fromEmail, $fromName)
            ->setTo($customer->getEmail(), $customer->getFirstName() . ' ' . $customer->getLastName())
            ->setBody($body, 'text/html');

        return 0 < $this->mailer->send($email);
    }

    /**
     * Notifies the administrator about the ticket message creation or update.
     *
     * @param array         $messages
     * @param UserInterface $admin
     *
     * @return int
     */
    public function sendTicketMessagesToAdmin(array $messages, UserInterface $admin = null)
    {
        foreach ($messages as $message) {
            if (!$message instanceof TicketMessageInterface) {
                throw new UnexpectedValueException("Expected instance of " . TicketMessageInterface::class);
            }
            if (!$message->isCustomer()) {
                throw new LogicException("Expected customer messages.");
            }
        }

        // TODO Translate with user's locale

        $fromEmail = $this->settingsManager->getParameter('notification.from_email');
        $fromName = $this->settingsManager->getParameter('notification.from_name');

        $subject = $this->translator->trans('ekyna_commerce.ticket_message.notify.admin.subject');

        $body = $this->templating->render('@EkynaCommerce/Email/admin_ticket_message.html.twig', [
            'subject'  => $subject,
            'messages' => $messages,
        ]);

        $email = new \Swift_Message();
        $email
            ->setSubject($subject)
            ->setFrom($fromEmail, $fromName)
            ->setTo($admin->getEmail(), $admin->getFullName() ? $admin->getFullName() : null)
            ->setBody($body, 'text/html');

        return 0 < $this->mailer->send($email);
    }

    /**
     * Sends the notification message.
     *
     * @param Notify $notify
     *
     * @return int
     */
    public function sendNotify(Notify $notify)
    {
        if ($notify->isEmpty()) {
            return 0;
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
            $content = $renderer->render(RendererInterface::FORMAT_PDF);
            $filename = $renderer->getFilename() . '.pdf';

            $message->attach(new \Swift_Attachment($content, $filename, 'application/pdf'));

            $attachments[$filename] = $this->translator->trans('ekyna_commerce.invoice.label.singular');
            $report .= "Attachment: $filename\n";
        }

        // Shipments
        foreach ($notify->getShipments() as $shipment) {
            $renderer = $this->rendererFactory->createRenderer($shipment, ShipmentRenderer::TYPE_BILL);
            $content = $renderer->render(RendererInterface::FORMAT_PDF);
            $filename = $renderer->getFilename() . '.pdf';

            $message->attach(new \Swift_Attachment($content, $filename, 'application/pdf'));

            $attachments[$filename] = $this->translator->trans(
                'ekyna_commerce.document.type.' . ($shipment->isReturn() ? 'return' : 'shipment') . '_bill'
            );
            $report .= "Attachment: $filename\n";
        }

        // Labels
        if (0 < $notify->getLabels()->count()) {
            $content = $this->shipmentLabelRenderer->render($notify->getLabels(), true);
            $filename = 'labels.pdf';

            $message->attach(new \Swift_Attachment($content, $filename, 'application/pdf'));

            $attachments[$filename] = $this->translator->trans(
                'ekyna_commerce.shipment_label.label.' . (1 < $notify->getLabels()->count() ? 'plural' : 'singular')
            );
            $report .= "Attachment: $filename\n";
        }

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
                $attachments[$filename] = $this->translator->trans(DocumentTypes::getLabel($type));
            } else {
                $attachments[$filename] = $attachment->getTitle();
            }
            $report .= "Attachment: $filename\n";
        }

        // SUBJECT
        $message->setSubject($notify->getSubject());
        $report .= "Subject: {$notify->getSubject()}\n";

        // CONTENT
        $source = $notify->getSource();
        if ($source instanceof SupplierOrderInterface) {
            // Supplier order form
            if ($notify->isIncludeForm()) {
                $renderer = $this->rendererFactory->createRenderer($source);
                $content = $renderer->render(RendererInterface::FORMAT_PDF);
                $filename = $renderer->getFilename() . '.pdf';

                $message->attach(new \Swift_Attachment($content, $filename, 'application/pdf'));

                $attachments[$filename] = $this->translator->trans('ekyna_commerce.document.type.form');
                $report .= "Attachment: $filename\n";
            }

            $content = $this->templating->render('@EkynaCommerce/Email/supplier_order_notify.html.twig', [
                'notify'      => $notify,
                'order'       => $source,
                'attachments' => $attachments,
            ]);
        } else {
            $content = $this->templating->render('@EkynaCommerce/Email/sale_notify.html.twig', [
                'notify'      => $notify,
                'sale'        => $this->getNotifySale($notify),
                'attachments' => $attachments,
            ]);
        }
        $message->setBody($content, 'text/html');

        if (!empty($notify->getCustomMessage())) {
            $report .= "Message: {$notify->getCustomMessage()}\n";
        }

        $notify->setReport($report);

        // Trigger imap copy
        if (!$notify->isTest()) {
            $message->getHeaders()->addTextHeader(ImapCopyPlugin::HEADER, 'do');
        }

        return $this->mailer->send($message);
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
        } elseif ($source instanceof PaymentInterface || $source instanceof ShipmentInterface) {
            return $source->getSale();
        }

        throw new RuntimeException("Failed to fetch the sale from the notify object.");
    }
}
