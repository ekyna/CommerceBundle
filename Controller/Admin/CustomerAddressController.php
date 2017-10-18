<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CustomerAddressController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerAddressController extends ResourceController
{
    /**
     * Invoice default action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function invoiceDefaultAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface $address */
        $address = $context->getResource($resourceName);
        $customer = $address->getCustomer();

        $this->isGranted('EDIT', $address);

        if (
            $address->isInvoiceDefault() && !(
                $customer->hasParent() &&
                null !== $customer->getParent()->getDefaultInvoiceAddress(true)
            )
        ) {
            return $this->redirect($this->generateResourcePath($address->getCustomer()));
        }

        $address->setInvoiceDefault(!$address->isInvoiceDefault());

        $event = $this->getOperator()->update($address);
        $event->toFlashes($this->getFlashBag());

        return $this->redirect($this->generateResourcePath($address->getCustomer()));
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
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface $address */
        $address = $context->getResource($resourceName);
        $customer = $address->getCustomer();

        $this->isGranted('EDIT', $address);

        if (
            $address->isDeliveryDefault() && !(
                $customer->hasParent() &&
                null !== $customer->getParent()->getDefaultDeliveryAddress(true)
            )
        ) {
            return $this->redirect($this->generateResourcePath($address->getCustomer()));
        }

        $address->setDeliveryDefault(!$address->isDeliveryDefault());

        $event = $this->getOperator()->update($address);
        $event->toFlashes($this->getFlashBag());

        return $this->redirect($this->generateResourcePath($address->getCustomer()));
    }

    /**
     * Choice list action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function choiceListAction(Request $request)
    {
        $this->isGranted('VIEW');

        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $customer */
        $customer = $this
            ->get('ekyna_commerce.customer.repository')
            ->find($request->attributes->get('customerId'));

        if (!$customer) {
            throw $this->createNotFoundException('Customer not found.');
        }

        $addresses = $this
            ->get('ekyna_commerce.customer_address.repository')
            ->findByCustomer($customer);

        $data = $this
            ->get('serializer')
            ->serialize(['choices' => $addresses], 'json', ['groups' => ['Default']]);

        return new Response($data, 200, ['Content-Type' => 'application/json']);
    }
}
