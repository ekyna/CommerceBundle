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
