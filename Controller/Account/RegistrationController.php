<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Event\RegistrationEvent;
use Ekyna\Bundle\CommerceBundle\Factory\CustomerFactoryInterface;
use Ekyna\Bundle\CommerceBundle\Model\Registration;
use Ekyna\Bundle\CommerceBundle\Repository\CustomerRepository;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UserBundle\Controller\Account\RegistrationController as BaseController;
use Ekyna\Bundle\UserBundle\Entity\Token;
use Ekyna\Bundle\UserBundle\Event\AccountEvent;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
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
    use CustomerTrait;

    private CustomerRepository       $customerRepository;
    private CustomerFactoryInterface $customerFactory;
    private ResourceManagerInterface $customerManager;

    public function setCustomerRepository(CustomerRepository $customerRepository): void
    {
        $this->customerRepository = $customerRepository;
    }

    public function setCustomerFactory(CustomerFactoryInterface $customerFactory): void
    {
        $this->customerFactory = $customerFactory;
    }

    public function setCustomerManager(ResourceManagerInterface $customerManager): void
    {
        $this->customerManager = $customerManager;
    }

    protected function redirectIfLoggedIn(): ?Response
    {
        if (!($this->userProvider->hasUser() && $this->customerProvider->hasCustomer())) {
            return null;
        }

        // TODO Flash message (?)

        $redirect = $this->urlGenerator->generate('ekyna_user_account_index');

        return new RedirectResponse($redirect);
    }

    public function register(Request $request): Response
    {
        $token = $this
            ->tokenManager
            ->findToken(
                $request->attributes->getAlnum('token'),
                Token::TYPE_REGISTRATION,
                false
            );

        if (!$token) {
            return new Response('Invalid token', Response::HTTP_FORBIDDEN);
        }

        /** @var UserInterface $user */
        if ($user = $this->userProvider->getUser()) {
            if ($this->customerRepository->findOneByUser($user)) {
                // TODO Flash ?

                return new RedirectResponse(
                    $this->urlGenerator->generate('ekyna_user_account_index')
                );
            }

            // Get rid of plain password validation
            $user->setPlainPassword('TEMP!#132');
        } else {
            $user = $this->userFactory->create();
            if ($email = $token->getData()['email'] ?? null) {
                $user->setEmail($email);
            }

            // Initialize event
            $accountEvent = new AccountEvent($user, null);
            $this->dispatcher->dispatch($accountEvent, AccountEvent::REGISTRATION_INITIALIZE);
            if ($response = $accountEvent->getResponse()) {
                return $response;
            }
        }

        $customer = $this->customerFactory->createWithUser($user);

        $registration = new Registration($customer);

        $registrationEvent = new RegistrationEvent($registration);
        $this->dispatcher->dispatch($registrationEvent, RegistrationEvent::REGISTRATION_INITIALIZE);

        if ($response = $registrationEvent->getResponse()) {
            return $response;
        }

        /* TODO (?) if ($targetPath = $request->query->get('target_path')) {
            $this->get('session')->set('fos_user_send_confirmation_email/target_path', $targetPath);
        }*/

        $form = $this->formFactory->create($this->config['form'], $registration);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if (0 < $user->getId()) {
                    // Remove the temp password
                    $user->setPlainPassword(null);
                }

                $registrationEvent = new RegistrationEvent($registration);
                $this->dispatcher->dispatch($registrationEvent, RegistrationEvent::REGISTRATION_SUCCESS);

                $user
                    ->setSendCreationEmail(true)
                    ->setEnabled(true);

                $this->userManager->persist($user);

                $resourceEvent = $this->customerManager->save($customer);

                if (!$resourceEvent->hasErrors()) {
                    $token->setUser($user);
                    $this->tokenManager->update($token);

                    $this->dispatcher->dispatch(
                        new RegistrationEvent($registration),
                        RegistrationEvent::REGISTRATION_COMPLETED
                    );

                    if ($response = $registrationEvent->getResponse()) {
                        return $response;
                    }

                    return new RedirectResponse(
                        $this->urlGenerator->generate('ekyna_user_account_registration_confirmed', [
                            'token' => $token->getHash(),
                        ])
                    );
                }

                FormUtil::addErrorsFromResourceEvent($form, $resourceEvent);
            }
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $content = $this->twig->render($this->config['template']['register'], [
            'form' => $form->createView(),
        ]);

        return (new Response($content))->setPrivate();
    }
}
