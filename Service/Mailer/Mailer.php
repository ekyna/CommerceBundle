<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Mailer;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\Notification;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererInterface;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
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
     * @param FilesystemInterface      $filesystem
     */
    public function __construct(
        \Swift_Mailer $transport,
        EngineInterface $templating,
        TranslatorInterface $translator,
        SettingsManagerInterface $settingsManager,
        RendererFactory $rendererFactory,
        FilesystemInterface $filesystem
    ) {
        $this->transport = $transport;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->settingsManager = $settingsManager;
        $this->rendererFactory = $rendererFactory;
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
     * Sends the notification message.
     *
     * @param Notification  $notification
     * @param SaleInterface $sale
     *
     * @return int
     */
    public function sendNotification(Notification $notification, SaleInterface $sale)
    {
        $message = new \Swift_Message();

        $content = $this->templating->render('EkynaCommerceBundle:Email:sale_notification.html.twig', [
            'notification' => $notification,
            'sale'         => $sale,
        ]);

        $message
            ->setSubject($notification->getSubject())
            ->setBody($content, 'text/html');

        // FROM
        $from = $notification->getFrom();
        $message->setFrom($from->getEmail(), $from->getName());

        // TO
        foreach ($notification->getRecipients() as $recipient) {
            $message->addTo($recipient->getEmail(), $recipient->getName());
        }
        foreach ($notification->getExtraRecipients() as $recipient) {
            $message->addTo($recipient->getEmail(), $recipient->getName());
        }

        // Copy
        foreach ($notification->getCopies() as $recipient) {
            $message->addCc($recipient->getEmail(), $recipient->getName());
        }
        foreach ($notification->getExtraCopies() as $recipient) {
            $message->addCc($recipient->getEmail(), $recipient->getName());
        }

        // Invoices
        foreach ($notification->getInvoices() as $invoice) {
            $renderer = $this->rendererFactory->createRenderer($invoice);
            $content = $renderer->render(RendererInterface::FORMAT_PDF);

            $attach = new \Swift_Attachment($content, $renderer->getFilename() . '.pdf', 'application/pdf');
            $message->attach($attach);
        }

        // Attachments
        foreach ($notification->getAttachments() as $attachment) {
            if (!$this->filesystem->has($path = $attachment->getPath())) {
                throw new RuntimeException("Attachment file '$path' not found.");
            }

            /** @var \League\Flysystem\File $file */
            $file = $this->filesystem->get($path);

            $attach = new \Swift_Attachment($file->read(), pathinfo($path, PATHINFO_BASENAME), $file->getMimetype());
            $message->attach($attach);
        }

        return $this->transport->send($message);
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
            $toEmail = $fromEmail;
        }

        /** @var \Swift_Mime_Message $message */
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail, $fromName)
            ->setTo($toEmail)
            ->setBody($body, 'text/html');

        return $this->transport->send($message);
    }
}
