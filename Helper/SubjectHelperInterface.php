<?php

namespace Ekyna\Bundle\CommerceBundle\Helper;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Subject\HelperInterface;

/**
 * Interface SubjectHelperInterface
 * @package Ekyna\Bundle\CommerceBundle\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SubjectHelperInterface extends HelperInterface
{
    /**
     * Returns the order item form options.
     *
     * @param OrderItemInterface $item
     * @param string             $property
     * @return array
     * @throws InvalidArgumentException
     */
    public function getFormOptions(OrderItemInterface $item, $property);
}
