<?php

namespace Ekyna\Bundle\CommerceBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Ekyna\Bundle\CommerceBundle\Model\Notification as Model;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class NotificationValidator
 * @package Ekyna\Bundle\CommerceBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotificationValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($notification, Constraint $constraint)
    {
        if (!$notification instanceof Model) {
            throw new InvalidArgumentException("Expected instance of " . Model::class);
        }
        if (!$constraint instanceof Notification) {
            throw new InvalidArgumentException("Expected instance of " . Notification::class);
        }

        if (0 === $notification->getRecipients()->count() && 0 === $notification->getExtraRecipients()->count()) {
            $this
                ->context
                ->buildViolation($constraint->pick_at_least_one_recipient)
                ->addViolation();
        }
    }
}
