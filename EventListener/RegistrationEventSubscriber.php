<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\RegistrationEvent;
use Ekyna\Bundle\CommerceBundle\Model\Registration;
use Ekyna\Bundle\CommerceBundle\Service\Mailer\Mailer;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AccountEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RegistrationEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var MailerInterface
     */
    private $fosMailer;

    /**
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var SessionInterface
     */
    private $session;


    /**
     * Constructor.
     *
     * @param Mailer                  $mailer
     * @param MailerInterface         $fosMailer
     * @param TokenGeneratorInterface $tokenGenerator
     * @param UrlGeneratorInterface   $urlGenerator
     * @param TranslatorInterface     $translator
     * @param SessionInterface        $session
     */
    public function __construct(
        Mailer $mailer,
        MailerInterface $fosMailer,
        TokenGeneratorInterface $tokenGenerator,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator,
        SessionInterface $session
    ) {
        $this->mailer = $mailer;
        $this->fosMailer = $fosMailer;
        $this->tokenGenerator = $tokenGenerator;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->session = $session;
    }

    /**
     * Account registration success event handler.
     *
     * @param RegistrationEvent $event
     */
    public function onRegistrationSuccess(RegistrationEvent $event)
    {
        $registration = $event->getRegistration();

        $this->buildDescription($registration);

        $this->sendConfirmationEmail($event);
    }

    /**
     * Account registration completed event handler.
     *
     * @param RegistrationEvent $event
     */
    public function onRegistrationCompleted(RegistrationEvent $event)
    {
        $customer = $event->getRegistration()->getCustomer();

        $this->mailer->sendAdminCustomerRegistration($customer);
    }

    /**
     * Builds the customer description.
     *
     * @param Registration $registration
     */
    private function buildDescription(Registration $registration)
    {
        $couples = [];

        if (null !== $applyGroup = $registration->getApplyGroup()) {
            $label = $this->translator->trans('ekyna_commerce.account.registration.field.apply_group');
            $couples[$label] = $applyGroup->getName();
        }

        if (null !== $contact = $registration->getInvoiceContact()) {
            if (!$contact->isIdentityEmpty()) {
                $label = $this->translator->trans('ekyna_commerce.account.registration.field.invoice_name');
                $identity = sprintf(
                    '%s %s %s',
                    $this->translator->trans('ekyna_commerce.gender.short.' . $contact->getGender()),
                    $contact->getFirstName(),
                    $contact->getLastName()
                );
                $couples[$label] = $identity;
            }
            if (!empty($email = $contact->getEmail())) {
                $label = $this->translator->trans('ekyna_commerce.account.registration.field.invoice_email');
                $couples[$label] = $email;
            }
            if (!empty($phone = $contact->getPhone())) {
                $label = $this->translator->trans('ekyna_commerce.account.registration.field.invoice_phone');
                $couples[$label] = $phone;
            }
        }

        if (!empty($comment = $registration->getComment())) {
            $label = $this->translator->trans('ekyna_core.field.comment');
            $couples[$label] = "\n" . $comment;
        }

        $description = '';
        foreach ($couples as $label => $value) {
            $description .= "$label : $value\n";
        }

        $registration->getCustomer()->setDescription($description);
    }

    /**
     * Sends the confirmation email.
     *
     * @param RegistrationEvent $event
     */
    private function sendConfirmationEmail(RegistrationEvent $event)
    {
        $user = $event->getRegistration()->getCustomer()->getUser();

        /**
         * Send FOSUB confirmation email only for new user.
         * (User may have been created by OAuthProvider)
         * @see \Ekyna\Bundle\UserBundle\Service\OAuth\FOSUserProvider::loadUserByOAuthUserResponse
         */
        if (null === $user->getId()) {
            $user->setEnabled(false);
            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }

            $this->fosMailer->sendConfirmationEmailMessage($user);

            $this->session->set('fos_user_send_confirmation_email/email', $user->getEmail());

            $url = $this->urlGenerator->generate('fos_user_registration_check_email');
            $event->setResponse(new RedirectResponse($url));
        }
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            RegistrationEvent::REGISTRATION_SUCCESS   => ['onRegistrationSuccess'],
            RegistrationEvent::REGISTRATION_COMPLETED => ['onRegistrationCompleted'],
        ];
    }
}
