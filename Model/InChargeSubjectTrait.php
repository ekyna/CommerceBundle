<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;

/**
 * Trait InChargeSubjectTrait
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait InChargeSubjectTrait
{
    protected ?UserInterface $inCharge = null;

    public function getInCharge(): ?UserInterface
    {
        return $this->inCharge;
    }

    public function setInCharge(?UserInterface $inCharge): InChargeSubjectInterface
    {
        $this->inCharge = $inCharge;

        return $this;
    }
}
