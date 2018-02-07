<?php

namespace Ekyna\Bundle\CommerceBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Notification
 * @package Ekyna\Bundle\CommerceBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Notification extends Constraint
{
    public $pick_at_least_one_recipient = 'ekyna_commerce.notification.pick_at_least_one_recipient';


    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
