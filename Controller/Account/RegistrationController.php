<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Account\RegistrationType;
use Ekyna\Bundle\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RegistrationController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RegistrationController extends BaseController
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function registerAction(Request $request)
    {
        /** @var $userManager UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        /** @var \Ekyna\Bundle\UserBundle\Model\UserInterface $user */
        $user = $userManager->createUser();
        $user->setEnabled(true);

        /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
        $customer = $this->get('ekyna_commerce.customer.repository')->createNew();
        $customer->setUser($user);

        // Default customer group
        $customer->setCustomerGroup(
            $this->get('ekyna_commerce.customer_group.repository')->findDefault()
        );

        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface $address */
        $address = $this->get('ekyna_commerce.customer_address.repository')->createNew();
        $address->setInvoiceDefault(true);
        $address->setDeliveryDefault(true);
        $customer->addAddress($address);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->createForm(RegistrationType::class, $customer, [
            'action' => $this->generateUrl('fos_user_registration_register'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // Copy fields from customer to default address
                $address = $customer->getAddresses()->first();
                $address
                    ->setGender($customer->getGender())
                    ->setFirstName($customer->getFirstName())
                    ->setLastName($customer->getLastName())
                    ->setCompany($customer->getCompany())
                    ->setPhone($customer->getPhone())
                    ->setMobile($customer->getMobile());

                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

                $userManager->updateUser($user);

                $em = $this->get('ekyna_commerce.customer_address.manager');
                $em->persist($customer);
                $em->flush();

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('fos_user_registration_confirmed');
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(
                    FOSUserEvents::REGISTRATION_COMPLETED,
                    new FilterUserResponseEvent($user, $request, $response)
                );

                return $response;
            }

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);

            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        return $this->render('EkynaCommerceBundle:Account/Registration:register.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
