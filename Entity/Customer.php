<?php

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Customer\Entity\Customer as BaseCustomer;

/**
 * Class Customer
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Customer extends BaseCustomer implements CustomerInterface
{
    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var UserInterface
     */
    protected $inCharge;


    /**
     * @inheritdoc
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @inheritdoc
     */
    public function setUser(UserInterface $user = null)
    {
        $this->user = $user;

        return $this;
    }


    /**
     * @inheritdoc
     */
    public function getInCharge()
    {
        return $this->inCharge;
    }

    /**
     * @inheritdoc
     */
    public function setInCharge(UserInterface $user = null)
    {
        $this->inCharge = $user;

        return $this;
    }
}
