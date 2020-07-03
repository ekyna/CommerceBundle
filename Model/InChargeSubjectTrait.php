<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;

/**
 * Trait InChargeSubjectTrait
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait InChargeSubjectTrait
{
    /**
     * @var UserInterface
     */
    protected $inCharge;


    /**
     * Returns the inCharge.
     *
     * @return UserInterface
     */
    public function getInCharge(): ?UserInterface
    {
        return $this->inCharge;
    }

    /**
     * Sets the inCharge.
     *
     * @param UserInterface $inCharge
     *
     * @return $this|InChargeSubjectInterface
     */
    public function setInCharge(UserInterface $inCharge = null): InChargeSubjectInterface
    {
        $this->inCharge = $inCharge;

        return $this;
    }
}
