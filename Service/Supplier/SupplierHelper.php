<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Supplier;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Supplier\Calculator\SupplierOrderCalculatorInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;

/**
 * Class SupplierHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Supplier
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierHelper
{
    private SupplierOrderCalculatorInterface $calculator;

    public function __construct(SupplierOrderCalculatorInterface $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @see SupplierOrderCalculatorInterface::calculateWeightTotal()
     */
    public function calculateWeightTotal(SupplierOrderInterface $order): Decimal
    {
        return $this->calculator->calculateWeightTotal($order);
    }

    /**
     * @see SupplierOrderCalculatorInterface::calculateItemsTotal()
     */
    public function calculateItemsTotal(SupplierOrderInterface $order): Decimal
    {
        return $this->calculator->calculateItemsTotal($order);
    }
}
