<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Event\RegistrationEvent;
use Ekyna\Bundle\CommerceBundle\Model\Registration;
use Ekyna\Bundle\CommerceBundle\Service\Mailer\Mailer;
use Ekyna\Bundle\CommerceBundle\Service\Newsletter\SubscriptionHelper;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AccountEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RegistrationEventSubscriber implements EventSubscriberInterface
{
    private Mailer              $mailer;
    private TranslatorInterface $translator;
    private ?SubscriptionHelper $subscriptionHelper;

    public function __construct(
        Mailer              $mailer,
        TranslatorInterface $translator,
        ?SubscriptionHelper $subscriptionHelper
    ) {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    public function onRegistrationSuccess(RegistrationEvent $event): void
    {
        $registration = $event->getRegistration();

        $this->buildDescription($registration);
    }

    public function onRegistrationCompleted(RegistrationEvent $event): void
    {
        $registration = $event->getRegistration();

        $this->subscribeToNewsletter($registration);

        $customer = $registration->getCustomer();

        // TODO Send confirmation email only for new user.
        $this->mailer->sendCustomerRegistrationConfirmation($customer);

        $this->mailer->sendAdminCustomerRegistration($customer);

        // TODO Update Cart informations
    }

    private function buildDescription(Registration $registration): void
    {
        $couples = [];

        if (null !== $applyGroup = $registration->getApplyGroup()) {
            $label = $this->translator->trans('account.registration.field.apply_group', [], 'EkynaCommerce');
            $couples[$label] = $applyGroup->getName();
        }

        if (null !== $contact = $registration->getInvoiceContact()) {
            if (!$contact->isIdentityEmpty()) {
                $label = $this->translator->trans('account.registration.field.invoice_name', [], 'EkynaCommerce');
                $identity = sprintf(
                    '%s %s %s',
                    $this->translator->trans('gender.short.' . $contact->getGender(), [], 'EkynaCommerce'),
                    $contact->getFirstName(),
                    $contact->getLastName()
                );
                $couples[$label] = $identity;
            }
            if (!empty($email = $contact->getEmail())) {
                $label = $this->translator->trans('account.registration.field.invoice_email', [], 'EkynaCommerce');
                $couples[$label] = $email;
            }
            if (!empty($phone = $contact->getPhone())) {
                $label = $this->translator->trans('account.registration.field.invoice_phone', [], 'EkynaCommerce');
                $couples[$label] = $phone;
            }
        }

        if (!empty($comment = $registration->getComment())) {
            $label = $this->translator->trans('field.comment', [], 'EkynaUi');
            $couples[$label] = "\n" . $comment;
        }

        $description = '';
        foreach ($couples as $label => $value) {
            $description .= "$label : $value\n";
        }

        $registration->getCustomer()->setDescription($description);
    }

    private function subscribeToNewsletter(Registration $registration): void
    {
        if (!$registration->isNewsletter() || !$this->subscriptionHelper) {
            return;
        }

        $customer = $registration->getCustomer();

        try {
            // TODO Schedule subscription in a message bus
            $this->subscriptionHelper->subscribeCustomerToDefaultAudiences($customer);
        } catch (Exception $e) {
            // Just fail silently
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RegistrationEvent::REGISTRATION_SUCCESS   => ['onRegistrationSuccess'],
            RegistrationEvent::REGISTRATION_COMPLETED => ['onRegistrationCompleted'],
        ];
    }
}
