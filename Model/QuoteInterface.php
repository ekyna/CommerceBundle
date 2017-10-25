<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface as BaseInterface;

/**
 * Interface QuoteInterface
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface QuoteInterface extends BaseInterface
{
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
     * @return $this|OrderInterface
     */
    public function setInCharge(UserInterface $user = null);
}
