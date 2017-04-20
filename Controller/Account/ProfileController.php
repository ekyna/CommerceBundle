<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\UserBundle\Controller\Account\ProfileController as BaseController;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

/**
 * Class InformationController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProfileController extends BaseController implements ControllerInterface
{
    use CustomerTrait;

    private ResourceManagerInterface $customerManager;

    public function setCustomerManager(ResourceManagerInterface $customerManager): void
    {
        $this->customerManager = $customerManager;
    }

    public function index(): Response
    {
        $content = $this->twig->render($this->config['template']['index'], [
            'user'     => $this->getUser(),
            'customer' => $this->getCustomer(),
        ]);

        return (new Response($content))->setPrivate();
    }

    public function edit(Request $request): Response
    {
        $customer = $this->getCustomer();

        $returnPath = $this->urlGenerator->generate('ekyna_user_account_profile');

        $form = $this->formFactory->create($this->config['form'], $customer, [
            'action'      => $this->urlGenerator->generate('ekyna_user_account_profile_edit'),
            'method'      => 'POST',
            'cancel_path' => $returnPath,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this
                ->customerManager
                ->save($customer);

            if (!$event->hasErrors()) {
                $this->flashHelper->addFlash(
                    t('account.information.edit.success', [], 'EkynaCommerce'),
                    'success'
                );

                return new RedirectResponse($returnPath);
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $content = $this->twig->render($this->config['template']['edit'], [
            'form' => $form->createView(),
        ]);

        return (new Response($content))->setPrivate();
    }
}
