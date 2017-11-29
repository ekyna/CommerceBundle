<?php

namespace Ekyna\Bundle\CommerceBundle\Event;

use Ekyna\Component\Commerce\Common\Event\SaleItemEvent;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class SaleItemValidationEvent
 * @package Ekyna\Bundle\CommerceBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemValidationEvent extends SaleItemEvent
{
    const VALIDATE = 'ekyna_commerce.sale_item.validate';

    /**
     * @var ExecutionContextInterface
     */
    private $context;


    /**
     * Constructor.
     *
     * @param SaleItemInterface         $item
     * @param ExecutionContextInterface $context
     */
    public function __construct(SaleItemInterface $item, ExecutionContextInterface $context)
    {
        parent::__construct($item);

        $this->context = $context;
    }

    /**
     * Returns the validation context.
     *
     * @return ExecutionContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }
}
