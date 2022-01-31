<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;

/**
 * Interface InChargeSubjectInterface
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InChargeSubjectInterface
{
    /**
     * Returns the 'in charge' user.
     */
    public function getInCharge(): ?UserInterface;

    /**
     * Sets the 'in charge' user.
     */
    public function setInCharge(?UserInterface $inCharge): InChargeSubjectInterface;
}
