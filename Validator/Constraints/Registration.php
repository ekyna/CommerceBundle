<?php

namespace Ekyna\Bundle\CommerceBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Registration
 * @package Ekyna\Bundle\CommerceBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Registration extends Constraint
{
    public $vat_number_is_mandatory = 'ekyna_commerce.customer.vat_number_is_mandatory';
    public $phone_or_mobile_is_mandatory = 'ekyna_commerce.customer.phone_or_mobile_is_mandatory';

    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
