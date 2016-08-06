<?php

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Bundle\AdminBundle\Event\ResourceEvent;
use Ekyna\Component\Commerce\Customer\Model\CustomerEventInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Class CustomerEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerEvent extends ResourceEvent implements CustomerEventInterface
{
    /**
     * Constructor.
     *
     * @param CustomerInterface $customer
     */
    public function __construct(CustomerInterface $customer)
    {
        $this->setResource($customer);
    }

    /**
     * @inheritdoc
     */
    public function getCustomer()
    {
        return $this->getResource();
    }
}
