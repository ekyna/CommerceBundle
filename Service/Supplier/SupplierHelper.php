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
    public function __construct(
        private readonly SupplierOrderCalculatorInterface $orderCalculator,
    ) {
    }

    /**
     * @see SupplierOrderCalculatorInterface::calculateWeightTotal()
     */
    public function calculateWeightTotal(SupplierOrderInterface $order): Decimal
    {
        return $this->orderCalculator->calculateWeightTotal($order);
    }

    /**
     * @see SupplierOrderCalculatorInterface::calculateItemsTotal()
     */
    public function calculateItemsTotal(SupplierOrderInterface $order): Decimal
    {
        return $this->orderCalculator->calculateItemsTotal($order);
    }

    /**
     * @see SupplierOrderCalculatorInterface::calculatePaymentTax()
     */
    public function calculateTax(SupplierOrderInterface $order): Decimal
    {
        return $this->orderCalculator->calculatePaymentTax($order);
    }

    /**
     * @see SupplierOrderCalculatorInterface::calculatePaymentTotal()
     */
    public function calculateTotal(SupplierOrderInterface $order): Decimal
    {
        return $this->orderCalculator->calculatePaymentTotal($order);
    }
}
