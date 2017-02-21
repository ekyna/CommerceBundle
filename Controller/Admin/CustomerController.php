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

        $customer->setCustomerGroup(
            $this->get('ekyna_commerce.customer_group.repository')->findDefault()
        );

        return $customer;
    }
}
