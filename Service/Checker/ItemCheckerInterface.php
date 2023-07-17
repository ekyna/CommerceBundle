<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Checker;

use Ekyna\Bundle\CommerceBundle\Exception\InvalidSaleItemException;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Interface ItemCheckerInterface
 * @package Ekyna\Bundle\CommerceBundle\Service\Checker
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface ItemCheckerInterface
{
    public function initialize(SaleInterface $sale): void;

    /**
     * @throws InvalidSaleItemException
     */
    public function check(SaleItemInterface $item): void;
}
