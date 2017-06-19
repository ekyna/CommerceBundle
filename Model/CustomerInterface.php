<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface as BaseInterface;

/**
 * Interface CustomerInterface
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CustomerInterface extends BaseInterface
{
    /**
     * Returns the user.
     *
     * @return UserInterface
     */
    public function getUser();

    /**
     * Sets the user.
     *
     * @param UserInterface $user
     *
     * @return $this|CustomerInterface
     */
    public function setUser(UserInterface $user = null);

    /**
     * Returns the 'in charge' user.
     *
     * @return UserInterface
     */
    public function getInCharge();

    /**
     * Sets the 'in charge' user.
     *
     * @param UserInterface $user
     *
     * @return $this|CustomerInterface
     */
    public function setInCharge(UserInterface $user = null);
}
