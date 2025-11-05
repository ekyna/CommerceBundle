<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Manufacture;

use Ekyna\Component\Commerce\Manufacture\Calculator\ProductionItemCalculator;
use Ekyna\Component\Commerce\Manufacture\Calculator\ProductionOrderCalculator;
use Ekyna\Component\Commerce\Manufacture\Model\BOMState;
use Ekyna\Component\Commerce\Manufacture\Model\POState;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionItemInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Commerce\Manufacture\Repository\BillOfMaterialsRepositoryInterface;

use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

use function sprintf;

/**
 * Class ProductionRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Manufacture
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ManufactureHelper
{
    public function __construct(
        private readonly ProductionOrderCalculator          $orderCalculator,
        private readonly ProductionItemCalculator           $itemCalculator,
        private readonly BillOfMaterialsRepositoryInterface $bomRepository,
    ) {
    }

    public function getBOMBySubject(SubjectInterface $subject): array
    {
        return $this->bomRepository->findBySubject($subject);
    }

    public function canOrderBeUpgraded(ProductionOrderInterface $order): bool
    {
        if (POState::isStockableState($order)) {
            return false;
        }

        if ($order->getBom()->getState() === BOMState::VALIDATED) {
            return false;
        }

        return null !== $this->bomRepository->findNewVersion($order->getBom());
    }

    public function canOrderBeProduced(ProductionOrderInterface $order): bool
    {
        $expected = $order->getQuantity();
        $produced = $this->orderCalculator->calculateProducedQuantity($order);

        return $expected !== $produced
            && $order->getBom()->getState() === BOMState::VALIDATED;
    }

    public function renderProductionOrderQuantity(ProductionOrderInterface $order): string
    {
        $expected = $order->getQuantity();
        $produced = $this->orderCalculator->calculateProducedQuantity($order);

        $theme = match (true) {
            $produced === 0         => 'danger',
            $produced === $expected => 'success',
            default                 => 'warning',
        };

        return sprintf(
            '<span class="label label-%s">%d / %d</span>',
            $theme,
            $produced,
            $expected
        );
    }

    public function renderItemMissingQuantity(ProductionItemInterface $item): string
    {
        $quantity = $this->itemCalculator->calculateMissingQuantity($item);

        if ($quantity->isZero()) {
            return '';
        }

        return sprintf(' <strong class="text-danger">(%s)</strong>', $quantity->toFixed());
    }
}
