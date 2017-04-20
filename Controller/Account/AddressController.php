<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerAddressType;
use Ekyna\Bundle\UiBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerAddressRepositoryInterface;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

use function Symfony\Component\Translation\t;

/**
 * Class AddressController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressController implements ControllerInterface
{
    use CustomerTrait;

    private CustomerAddressRepositoryInterface $addressRepository;
    private ResourceFactoryInterface           $addressFactory;
    private ResourceManagerInterface           $addressManager;
    private Environment                        $twig;
    private FormFactoryInterface               $formFactory;
    private FlashHelper                        $flashHelper;
    private UrlGeneratorInterface              $urlGenerator;

    public function __construct(
        CustomerAddressRepositoryInterface $addressRepository,
        ResourceFactoryInterface           $addressFactory,
        ResourceManagerInterface           $addressManager,
        Environment                        $twig,
        FormFactoryInterface               $formFactory,
        FlashHelper                        $flashHelper,
        UrlGeneratorInterface              $urlGenerator
    ) {
        $this->addressRepository = $addressRepository;
        $this->addressFactory = $addressFactory;
        $this->addressManager = $addressManager;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->flashHelper = $flashHelper;
        $this->urlGenerator = $urlGenerator;
    }

    public function index(): Response
    {
        $customer = $this->getCustomer();

        $parameters = [];

        $parameters['addresses'] = $this
            ->addressRepository
            ->findByCustomer($customer);

        if (null !== $parent = $customer->getParent()) {
            $parameters['parent_addresses'] = $this
                ->addressRepository
                ->findByCustomer($parent);
        }

        $content = $this->twig->render('@EkynaCommerce/Account/Address/index.html.twig', $parameters);

        return (new Response($content))->setPrivate();
    }

    public function add(Request $request): Response
    {
        $customer = $this->getCustomer();

        /** @var CustomerAddressInterface $address */
        $address = $this->addressFactory->create();
        $address->setCustomer($customer);

        $form = $this->formFactory->create(CustomerAddressType::class, $address, [
            'method' => 'POST',
            'action' => $this->urlGenerator->generate('ekyna_commerce_account_address_add'),
        ]);

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_address_index');

        FormUtil::addFooter($form, [
            'cancel_path' => $redirect,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->addressManager->update($address);

            if (!$event->hasErrors()) {
                $this->flashHelper->addFlash(t('account.address.edit.success', [], 'EkynaCommerce'), 'success');

                return new RedirectResponse($redirect);
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $addresses = $this
            ->addressRepository
            ->findByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Address/add.html.twig', [
            'form'      => $form->createView(),
            'addresses' => $addresses,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function edit(Request $request): Response
    {
        $customer = $this->getCustomer();

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_address_index');

        if (!$address = $this->findAddressByRequest($request)) {
            return new RedirectResponse($redirect);
        }

        $form = $this->formFactory->create(CustomerAddressType::class, $address, [
            'method' => 'POST',
            'action' => $this->urlGenerator->generate('ekyna_commerce_account_address_edit', [
                'addressId' => $address->getId(),
            ]),
        ]);

        FormUtil::addFooter($form, [
            'cancel_path' => $redirect,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this
                ->addressManager
                ->update($address);

            if (!$event->hasErrors()) {
                $this->flashHelper->addFlash(t('account.address.edit.success', [], 'EkynaCommerce'), 'success');

                return new RedirectResponse($redirect);
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $addresses = $this
            ->addressRepository
            ->findByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Address/edit.html.twig', [
            'form'      => $form->createView(),
            'addresses' => $addresses,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function remove(Request $request): Response
    {
        $customer = $this->getCustomer();

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_address_index');

        if (!$address = $this->findAddressByRequest($request)) {
            return new RedirectResponse($redirect);
        }

        $form = $this->formFactory->create(ConfirmType::class, null, [
            'method'      => 'POST',
            'action'      => $this->urlGenerator->generate('ekyna_commerce_account_address_remove', [
                'addressId' => $address->getId(),
            ]),
            'message'     => t('account.address.remove.confirm', [], 'EkynaCommerce'),
            'cancel_path' => $redirect,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this
                ->addressManager
                ->delete($address);

            if (!$event->hasErrors()) {
                $this->flashHelper->addFlash(t('account.address.remove.success', [], 'EkynaCommerce'), 'success');

                return new RedirectResponse($redirect);
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $addresses = $this
            ->addressRepository
            ->findByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Address/remove.html.twig', [
            'address'   => $address,
            'form'      => $form->createView(),
            'addresses' => $addresses,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function invoiceDefault(Request $request): Response
    {
        $customer = $this->getCustomer();

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_address_index');

        if (!$address = $this->findAddressByRequest($request)) {
            return new RedirectResponse($redirect);
        }

        if (
            $address->isInvoiceDefault()
            && !(
                $customer->hasParent()
                && null !== $customer->getParent()->getDefaultInvoiceAddress(true)
            )
        ) {
            return new RedirectResponse($redirect);
        }

        $address->setInvoiceDefault(!$address->isInvoiceDefault());

        $event = $this
            ->addressManager
            ->update($address);

        if ($event->hasErrors()) {
            $this->flashHelper->fromEvent($event);
        } else {
            $this->flashHelper->addFlash(t('account.address.edit.success', [], 'EkynaCommerce'), 'success');
        }

        return new RedirectResponse($redirect);
    }

    public function deliveryDefault(Request $request): Response
    {
        $customer = $this->getCustomer();

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_address_index');

        if (!$address = $this->findAddressByRequest($request)) {
            return new RedirectResponse($redirect);
        }

        if (
            $address->isDeliveryDefault()
            && !(
                $customer->hasParent()
                && null !== $customer->getParent()->getDefaultDeliveryAddress(true)
            )
        ) {
            return new RedirectResponse($redirect);
        }

        $address->setDeliveryDefault(!$address->isDeliveryDefault());

        $event = $this
            ->addressManager
            ->update($address);

        if ($event->hasErrors()) {
            $this->flashHelper->fromEvent($event);
        } else {
            $this->flashHelper->addFlash(t('account.address.edit.success', [], 'EkynaCommerce'), 'success');
        }

        return new RedirectResponse($redirect);
    }

    protected function findAddressByRequest(Request $request): ?CustomerAddressInterface
    {
        $customer = $this->getCustomer();

        $id = $request->attributes->getInt('addressId');

        if (0 >= $id) {
            return null;
        }

        return $this
            ->addressRepository
            ->findOneBy([
                'id'       => $id,
                'customer' => $customer,
            ]);
    }
}
