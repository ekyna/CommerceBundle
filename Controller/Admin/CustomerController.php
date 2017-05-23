<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;

/**
 * Class CustomerController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerController extends ResourceController
{
    /**
     * @inheritDoc
     */
    protected function createNew(Context $context)
    {
        /** @var \Ekyna\Bundle\CommerceBundle\Entity\Customer $customer */
        $customer = parent::createNew($context);

        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $parent */
        $parent = $this->getRepository()->find($context->getRequest()->query->get('parent'));
        if (null !== $parent) {
            $customer
                ->setParent($parent)
                ->setCustomerGroup($parent->getCustomerGroup());
        } else {
            $customer->setCustomerGroup(
                $this->get('ekyna_commerce.customer_group.repository')->findDefault()
            );
        }

        return $customer;
    }

    /**
     * @inheritDoc
     */
    protected function buildShowData(
        /** @noinspection PhpUnusedParameterInspection */
        array &$data,
        /** @noinspection PhpUnusedParameterInspection */
        Context $context
    ) {
        $request = $context->getRequest();
        $customer = $context->getResource();

        // Children
        $children = $this
            ->getTableFactory()
            ->createBuilder('ekyna_commerce_customer', [
                'name'       => 'ekyna_commerce_customer_children',
                'sortable'   => false,
                'filterable' => false,
                'parent'     => $customer,
            ])
            ->getTable($request);

        $data['children'] = $children->createView();

        // Quotes
        $quotes = $this
            ->getTableFactory()
            ->createBuilder('ekyna_commerce_quote', [
                'name'       => 'ekyna_commerce_customer_quote',
                'sortable'   => false,
                'filterable' => false,
                'customer'   => $customer,
            ])
            ->getTable();

        $data['quotes'] = $quotes->createView();

        // Orders
        $orders = $this
            ->getTableFactory()
            ->createBuilder('ekyna_commerce_order', [
                'name'       => 'ekyna_commerce_customer_order',
                'sortable'   => false,
                'filterable' => false,
                'customer'   => $customer,
            ])
            ->getTable();

        $data['orders'] = $orders->createView();
    }
}
