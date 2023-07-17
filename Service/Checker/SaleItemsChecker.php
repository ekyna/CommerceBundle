<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Checker;

use Ekyna\Bundle\CommerceBundle\Exception\InvalidSaleItemException;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;

/**
 * Class SaleItemsChecker
 * @package Ekyna\Bundle\CommerceBundle\Service\Checker
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemsChecker
{
    public const INVALID_ITEM = 'invalid_item';

    /**
     * @var array<int, ItemCheckerInterface>
     */
    private array $checkers = [];

    public function registerChecker(ItemCheckerInterface $checker): void
    {
        $this->checkers[] = $checker;
    }

    /**
     * Checks the given sale's items for integrity regarding subject providers.
     *
     * @param SaleInterface $sale
     * @return bool
     */
    public function check(SaleInterface $sale): bool
    {
        foreach ($this->checkers as $checker) {
            $checker->initialize($sale);
        }

        $valid = true;

        foreach ($sale->getItems() as $item) {
            try {
                foreach ($this->checkers as $checker) {
                    $checker->check($item);
                }
            } catch (InvalidSaleItemException) {
                $item->setDatum(self::INVALID_ITEM, 1);
                $valid = false;
            }
        }

        return $valid;
    }
}
