<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerAddressType;
use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AddressController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressController extends AbstractController
{
    /**
     * Address index action.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $customer = $this->getCustomerOrRedirect();

        $parameters = [];

        $parameters['addresses'] = $this
            ->get('ekyna_commerce.customer_address.repository')
            ->findByCustomer($customer);

        if (null !== $parent = $customer->getParent()) {
            $parameters['parent_addresses'] = $this
                ->get('ekyna_commerce.customer_address.repository')
                ->findByCustomer($parent);
        }

        return $this->render('@EkynaCommerce/Account/Address/index.html.twig', $parameters);
    }

    /**
     * Add address action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface $address */
        $address = $this
            ->get('ekyna_commerce.customer_address.repository')
            ->createNew();

        $address->setCustomer($customer);

        $form = $this->createForm(CustomerAddressType::class, $address, [
            'method' => 'POST',
            'action' => $this->generateUrl('ekyna_commerce_account_address_add'),
        ]);

        $this->createFormFooter($form, [
            'cancel_path' => $this->generateUrl('ekyna_commerce_account_address_index'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this
                ->get('ekyna_commerce.customer_address.operator')
                ->update($address);

            if (!$event->hasErrors()) {
                $this->addFlash('ekyna_commerce.account.address.edit.success', 'success');

                return $this->redirectToRoute('ekyna_commerce_account_address_index');
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $addresses = $this
            ->get('ekyna_commerce.customer_address.repository')
            ->findByCustomer($customer);

        return $this->render('@EkynaCommerce/Account/Address/add.html.twig', [
            'form'      => $form->createView(),
            'addresses' => $addresses,
        ]);
    }

    /**
     * Edit address action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        if (null === $address = $this->findAddressByRequest($request)) {
            return $this->redirectToRoute('ekyna_commerce_account_address_index');
        }

        $form = $this
            ->createForm(CustomerAddressType::class, $address, [
                'method' => 'POST',
                'action' => $this->generateUrl('ekyna_commerce_account_address_edit', [
                    'addressId' => $address->getId(),
                ]),
            ]);

        $this->createFormFooter($form, [
            'cancel_path' => $this->generateUrl('ekyna_commerce_account_address_index'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this
                ->get('ekyna_commerce.customer_address.operator')
                ->update($address);

            if (!$event->hasErrors()) {
                $this->addFlash('ekyna_commerce.account.address.edit.success', 'success');

                return $this->redirectToRoute('ekyna_commerce_account_address_index');
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $addresses = $this
            ->get('ekyna_commerce.customer_address.repository')
            ->findByCustomer($customer);

        return $this->render('@EkynaCommerce/Account/Address/edit.html.twig', [
            'form' => $form->createView(),
            'addresses' => $addresses,
        ]);
    }

    /**
     * Remove address action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        if (null === $address = $this->findAddressByRequest($request)) {
            return $this->redirectToRoute('ekyna_commerce_account_address_index');
        }

        $form = $this
            ->createForm(ConfirmType::class, null, [
                'method'      => 'POST',
                'action'      => $this->generateUrl('ekyna_commerce_account_address_remove', [
                    'addressId' => $address->getId(),
                ]),
                'message'     => 'ekyna_commerce.account.address.remove.confirm',
                'cancel_path' => $this->generateUrl('ekyna_commerce_account_address_index'),
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this
                ->get('ekyna_commerce.customer_address.operator')
                ->delete($address);

            if (!$event->hasErrors()) {
                $this->addFlash('ekyna_commerce.account.address.remove.success', 'success');

                return $this->redirectToRoute('ekyna_commerce_account_address_index');
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $addresses = $this
            ->get('ekyna_commerce.customer_address.repository')
            ->findByCustomer($customer);

        return $this->render('@EkynaCommerce/Account/Address/remove.html.twig', [
            'address' => $address,
            'form'    => $form->createView(),
            'addresses' => $addresses,
        ]);
    }

    /**
     * Invoice default action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function invoiceDefaultAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        if (null === $address = $this->findAddressByRequest($request)) {
            return $this->redirectToRoute('ekyna_commerce_account_address_index');
        }

        if (
            $address->isInvoiceDefault() && !(
                $customer->hasParent() &&
                null !== $customer->getParent()->getDefaultInvoiceAddress(true)
            )
        ) {
            return $this->redirectToRoute('ekyna_commerce_account_address_index');
        }

        $address->setInvoiceDefault(!$address->isInvoiceDefault());

        $event = $this
            ->get('ekyna_commerce.customer_address.operator')
            ->update($address);

        if ($event->hasErrors()) {
            $event->toFlashes($this->getFlashBag());
        } else {
            $this->addFlash('ekyna_commerce.account.address.edit.success', 'success');
        }

        return $this->redirectToRoute('ekyna_commerce_account_address_index');
    }

    /**
     * Delivery default action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deliveryDefaultAction(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        if (null === $address = $this->findAddressByRequest($request)) {
            return $this->redirectToRoute('ekyna_commerce_account_address_index');
        }

        if (
            $address->isDeliveryDefault() && !(
                $customer->hasParent() &&
                null !== $customer->getParent()->getDefaultDeliveryAddress(true)
            )
        ) {
            return $this->redirectToRoute('ekyna_commerce_account_address_index');
        }

        $address->setDeliveryDefault(!$address->isDeliveryDefault());

        $event = $this
            ->get('ekyna_commerce.customer_address.operator')
            ->update($address);

        if ($event->hasErrors()) {
            $event->toFlashes($this->getFlashBag());
        } else {
            $this->addFlash('ekyna_commerce.account.address.edit.success', 'success');
        }

        return $this->redirectToRoute('ekyna_commerce_account_address_index');
    }

    /**
     * Finds the address by request.
     *
     * @param Request $request
     *
     * @return \Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface
     */
    protected function findAddressByRequest(Request $request)
    {
        $customer = $this->getCustomerOrRedirect();

        $id = intval($request->attributes->get('addressId'));

        if (0 < $id) {
            return $this
                ->get('ekyna_commerce.customer_address.repository')
                ->findOneBy([
                    'id'       => $id,
                    'customer' => $customer,
                ]);
        }

        return null;
    }
}
