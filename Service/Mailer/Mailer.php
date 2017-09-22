<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Mailer;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\Notification;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
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
     * Constructor.
     *
     * @param \Swift_Mailer            $transport
     * @param EngineInterface          $templating
     * @param TranslatorInterface      $translator
     * @param SettingsManagerInterface $settingsManager
     */
    public function __construct(
        \Swift_Mailer $transport,
        EngineInterface $templating,
        TranslatorInterface $translator,
        SettingsManagerInterface $settingsManager
    ) {
        $this->transport = $transport;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->settingsManager = $settingsManager;
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

    private function buildCustomerSummary(CustomerInterface $customer)
    {
        /*$fields = [

        ]*/
    }

    public function sendNotification(Notification $notification)
    {
        // TODO $message = \Swift_Message::newInstance();
        //$message->setCc
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
