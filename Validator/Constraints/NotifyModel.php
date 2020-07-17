<?php

namespace Ekyna\Bundle\CommerceBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class NotifyModel
 * @package Ekyna\Bundle\CommerceBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyModel extends Constraint
{
    public $subject_required = 'ekyna_commerce.notify_model.subject_required';
    public $duplicate_type   = 'ekyna_commerce.notify_model.duplicate_type';

    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
