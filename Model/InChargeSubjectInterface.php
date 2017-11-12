<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\UserBundle\Model\UserInterface;

/**
 * Interface InChargeSubjectInterface
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InChargeSubjectInterface
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
     * @param UserInterface $inCharge
     *
     * @return $this|InChargeSubjectInterface
     */
    public function setInCharge(UserInterface $inCharge = null);
}
