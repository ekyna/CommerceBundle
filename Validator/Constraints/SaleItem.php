<?php

namespace Ekyna\Bundle\CommerceBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class SaleItem
 * @package Ekyna\Bundle\CommerceBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItem extends Constraint
{
    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
