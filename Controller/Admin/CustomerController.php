<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CommerceBundle\Table\Type;

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
    protected function buildShowData(array &$data, Context $context)
    {
        $request = $context->getRequest();
        $customer = $context->getResource();

        $tables = [
            'children' => [
                'type' => Type\CustomerType::class,
                'options' => [
                    'parent' => $customer,
                ]
            ],
            'quotes' => [
                'type' => Type\QuoteType::class,
                'options' => [
                    'customer' => $customer,
                ]
            ],
            'orders' => [
                'type' => Type\OrderType::class,
                'options' => [
                    'customer' => $customer,
                ]
            ],
        ];

        foreach ($tables as $name => $config) {
            $table = $this
                ->getTableFactory()
                ->createTable($name, $config['type'], $config['options']);

            if (null !== $response = $table->handleRequest($request)) {
                return $response;
            }

            $data[$name] = $table->createView();
        }

        return null;
    }
}
