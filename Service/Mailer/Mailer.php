<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Mailer;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\DocumentTypes;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderSubmit;
use Ekyna\Bundle\CommerceBundle\Service\Shipment\LabelRenderer as ShipmentLabelRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Subject\LabelRenderer as SubjectLabelRenderer;
use Ekyna\Bundle\CoreBundle\Service\SwiftMailer\ImapCopyPlugin;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererInterface;
use Ekyna\Bundle\CommerceBundle\Service\Document\ShipmentRenderer;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Common\Model\Recipient;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
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
     * @var \Swift_Mailer
     */
    protected $transport;

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
     * @param \Swift_Mailer            $transport
     * @param EngineInterface          $templating
     * @param TranslatorInterface      $translator
     * @param SettingsManagerInterface $settingsManager
     * @param RendererFactory          $rendererFactory
     * @param ShipmentLabelRenderer    $shipmentLabelRenderer
     * @param SubjectLabelRenderer     $subjectLabelRenderer
     * @param SubjectHelperInterface $subjectHelper
     * @param FilesystemInterface      $filesystem
     */
    public function __construct(
        \Swift_Mailer $transport,
        EngineInterface $templating,
        TranslatorInterface $translator,
        SettingsManagerInterface $settingsManager,
        RendererFactory $rendererFactory,
        ShipmentLabelRenderer $shipmentLabelRenderer,
        SubjectLabelRenderer $subjectLabelRenderer,
        SubjectHelperInterface $subjectHelper,
        FilesystemInterface $filesystem
    ) {
        $this->transport = $transport;
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
     * Notifies the administrator about a customer registration.
     *
     * @param CustomerInterface $customer
     *
     * @return integer
     */
    public function sendAdminCustomerRegistration(CustomerInterface $customer)
    {
        $body = $this->templating->render('EkynaCommerceBundle:Email:admin_customer_registration.html.twig', [
            'customer' => $customer,
        ]);

        $subject = $this->translator->trans('ekyna_commerce.account.registration.notify.subject');

        return $this->sendEmail($body, $subject);
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

        $message = new \Swift_Message();
        $message
            ->setSubject('Bon de commande ' . $order->getNumber()) // TODO translate with supplier locale
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

            $message->attach(\Swift_Attachment::newInstance(
                $this->subjectLabelRenderer->render($labels),
                'labels.pdf',
                'application/pdf'
            ));
        }

        // Trigger imap copy
        $message->getHeaders()->addTextHeader(ImapCopyPlugin::HEADER, 'do');

        return 0 < $this->transport->send($message);
    }

    /**
     * Sends the notification message.
     *
     * @param \Ekyna\Component\Commerce\Common\Model\Notify $notify
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

        $report .= "From: {$this->formatRecipient($notify->getFrom())}\n";

        // TO
        foreach ($notify->getRecipients() as $recipient) {
            $message->addTo($recipient->getEmail(), $recipient->getName());
            $report .= "To: {$this->formatRecipient($notify->getFrom())}\n";
        }
        foreach ($notify->getExtraRecipients() as $recipient) {
            $message->addTo($recipient->getEmail(), $recipient->getName());
            $report .= "To: {$this->formatRecipient($notify->getFrom())}\n";
        }

        // Copy
        foreach ($notify->getCopies() as $recipient) {
            $message->addCc($recipient->getEmail(), $recipient->getName());
            $report .= "Cc: {$this->formatRecipient($notify->getFrom())}\n";
        }
        foreach ($notify->getExtraCopies() as $recipient) {
            $message->addCc($recipient->getEmail(), $recipient->getName());
            $report .= "Cc: {$this->formatRecipient($notify->getFrom())}\n";
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
        $content = $this->templating->render('EkynaCommerceBundle:Email:sale_notify.html.twig', [
            'notify'      => $notify,
            'sale'        => $this->getNotifySale($notify),
            'attachments' => $attachments,
        ]);
        $message->setBody($content, 'text/html');

        $notify->setReport($report);

        // Trigger imap copy
        $message->getHeaders()->addTextHeader(ImapCopyPlugin::HEADER, 'do');

        return $this->transport->send($message);
    }

    /**
     * Returns the sale from the notify object.
     *
     * @param \Ekyna\Component\Commerce\Common\Model\Notify $notify
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

    /**
     * Formats the recipient.
     *
     * @param \Ekyna\Component\Commerce\Common\Model\Recipient $recipient
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
     * Sends the email.
     *
     * @param string $body
     * @param string $toEmail
     * @param string $subject
     *
     * @return integer
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

        return $this->transport->send($message);
    }
}
