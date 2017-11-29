<?php

namespace Ekyna\Bundle\CommerceBundle\Validator\Constraints;

use Ekyna\Bundle\CommerceBundle\Event\SaleItemValidationEvent;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class SaleItemValidator
 * @package Ekyna\Bundle\CommerceBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemValidator extends ConstraintValidator
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;


    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @inheritdoc
     */
    public function validate($item, Constraint $constraint)
    {
        if (null === $item) {
            return;
        }

        if (!$item instanceof SaleItemInterface) {
            throw new UnexpectedTypeException($item, SaleItemInterface::class);
        }
        if (!$constraint instanceof SaleItem) {
            throw new UnexpectedTypeException($constraint, SaleItem::class);
        }

        $this->eventDispatcher->dispatch(
            SaleItemValidationEvent::VALIDATE,
            new SaleItemValidationEvent($item, $this->context)
        );
    }
}
