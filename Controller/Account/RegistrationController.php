<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Event\RegistrationEvent;
use Ekyna\Bundle\CommerceBundle\Form\Type\Account\RegistrationType;
use Ekyna\Bundle\CommerceBundle\Model\Registration;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Bundle\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Event\GetResponseUserEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class RegistrationController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RegistrationController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function registerAction(Request $request)
    {
        // TODO if user is logged and linked to a customer, redirect to customer area index

        /** @var $userManager UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        if (null === $user = $this->get('ekyna_user.user.provider')->getUser()) {
            /** @var \Ekyna\Bundle\UserBundle\Model\UserInterface $user */
            $user = $userManager->createUser();
            $user->setEnabled(true);
        } else {
            // Get ride of plain password validation
            $user->setPlainPassword('TEMP!#132');
        }

        /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
        $customer = $this->get('ekyna_commerce.customer.repository')->createNew();
        $customer->setUser($user);

        // Default customer group
        $customer->setCustomerGroup(
            $this->get('ekyna_commerce.customer_group.repository')->findDefault()
        );

        /*$event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);
        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }*/

        $registration = new Registration($customer);

        $event = new RegistrationEvent($registration);
        $dispatcher->dispatch(RegistrationEvent::REGISTRATION_INITIALIZE, $event);

        $form = $this->createForm(RegistrationType::class, $registration, [
            'action' => $this->generateUrl('fos_user_registration_register'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if (0 < $user->getId()) {
                    // Remove the temp password
                    $user->setPlainPassword(null);
                }

                $dispatcher->dispatch(RegistrationEvent::REGISTRATION_SUCCESS, $event);

                // This FOSUB event is disabled because we need the customer (and not only the user)
                // to build the confirmation email).
                /*$event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);*/

                /** @noinspection PhpMethodParametersCountMismatchInspection */
                $userManager->updateUser($user, false);

                $em = $this->get('ekyna_commerce.customer_address.manager');
                $em->persist($customer);
                $em->flush();

                $dispatcher->dispatch(RegistrationEvent::REGISTRATION_COMPLETED, new RegistrationEvent($registration));

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('fos_user_registration_confirmed');
                    $response = new RedirectResponse($url);
                }

                // We keep this FOSUB event for auto login
                $dispatcher->dispatch(
                    FOSUserEvents::REGISTRATION_COMPLETED,
                    new FilterUserResponseEvent($user, $request, $response)
                );

                return $response;
            }

            /*$event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);

            if (null !== $response = $event->getResponse()) {
                return $response;
            }*/
        }

        return $this->render('@EkynaCommerce/Account/Registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Tell the user to check his email provider.
     */
    public function checkEmailAction()
    {
        $email = $this->get('session')->get('fos_user_send_confirmation_email/email');

        if (empty($email)) {
            return $this->redirect($this->generateUrl('fos_user_registration_register'));
        }

        $this->get('session')->remove('fos_user_send_confirmation_email/email');
        $user = $this->get('fos_user.user_manager')->findUserByEmail($email);

        if (null === $user) {
            // TODO Flash + redirect
            throw $this->createNotFoundException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->render('@EkynaCommerce/Account/Registration/check_email.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Receive the confirmation token from user email provider, login the user.
     *
     * @param Request $request
     * @param string  $token
     *
     * @return Response
     */
    public function confirmAction(Request $request, $token)
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            // TODO Flash + redirect
            throw $this->createNotFoundException(
                sprintf('The user with confirmation token "%s" does not exist', $token)
            );
        }

        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user->setConfirmationToken(null);
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);

        $userManager->updateUser($user);

        if (null === $response = $event->getResponse()) {
            $url = $this->generateUrl('fos_user_registration_confirmed');
            $response = new RedirectResponse($url);
        }

        $dispatcher->dispatch(
            FOSUserEvents::REGISTRATION_CONFIRMED,
            new FilterUserResponseEvent($user, $request, $response)
        );

        return $response;
    }

    /**
     * Tell the user his account is now confirmed.
     */
    public function confirmedAction()
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            // TODO Flash + redirect (?)
            throw $this->createAccessDeniedException('This user does not have access to this section.');
        }

        $targetUrl = $this->generateUrl('ekyna_user_account_index');
        $token = $this->get('security.token_storage')->getToken();

        if (null !== $token && $token instanceof UsernamePasswordToken) {
            $key = sprintf('_security.%s.target_path', $token->getProviderKey());
            if ($this->get('session')->has($key)) {
                $targetUrl = $this->get('session')->get($key);
            }
        }

        return $this->render('@EkynaCommerce/Account/Registration/confirmed.html.twig', [
            'user'      => $user,
            'targetUrl' => $targetUrl,
        ]);
    }
}
