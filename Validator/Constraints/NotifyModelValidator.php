<?php

namespace Ekyna\Bundle\CommerceBundle\Validator\Constraints;

use Ekyna\Bundle\CommerceBundle\Entity\NotifyModel as Entity;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class NotifyModelValidator
 * @package Ekyna\Bundle\CommerceBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyModelValidator extends ConstraintValidator
{
    /**
     * @var ResourceRepositoryInterface
     */
    private $repository;

    /**
     * @var string
     */
    private $defaultLocale;


    /**
     * Constructor.
     *
     * @param ResourceRepositoryInterface $repository
     * @param string                      $defaultLocale
     */
    public function __construct(ResourceRepositoryInterface $repository, string $defaultLocale)
    {
        $this->repository    = $repository;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @inheritDoc
     */
    public function validate($model, Constraint $constraint)
    {
        if (!$model instanceof Entity) {
            throw new UnexpectedTypeException($model, Entity::class);
        }
        if (!$constraint instanceof NotifyModel) {
            throw new UnexpectedTypeException($constraint, NotifyModel::class);
        }

        $type = $model->getType();

        if (NotificationTypes::MANUAL === $type) {
            if (!empty($model->translate($this->defaultLocale)->getSubject())) {
                return;
            }

            $this
                ->context
                ->buildViolation($constraint->subject_required)
                ->atPath("translations[{$this->defaultLocale}].subject")
                ->addViolation();

            return;
        }

        /** @var Entity $found */
        if (!$found = $this->repository->findOneBy(['type' => $type])) {
            return;
        }

        if ($found->getId() === $model->getId()) {
            return;
        }

        $this
            ->context
            ->buildViolation($constraint->duplicate_type)
            ->atPath('type')
            ->addViolation();
    }
}
