<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Manufacture\ManufactureHelper;
use Ekyna\Bundle\CommerceBundle\Service\Manufacture\ManufactureRenderer;
use Ekyna\Component\Commerce\Manufacture\Calculator\BillOfMaterialsCalculator;
use Ekyna\Component\Commerce\Manufacture\Calculator\ProductionPriceCalculator;
use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Manufacture\Model\BOMState;
use Ekyna\Component\Commerce\Manufacture\Model\POState;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Class ManufactureExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ManufactureExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'render_subject_production_orders',
                [ManufactureRenderer::class, 'renderSubjectProductionOrders'],
                ['is_safe' => ['html']]
            )
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'bill_of_materials_cost',
                [BillOfMaterialsCalculator::class, 'calculateBOMCost']
            ),
            new TwigFilter(
                'bom_component_total_cost',
                [BillOfMaterialsCalculator::class, 'calculateComponentTotalCost']
            ),
            new TwigFilter(
                'production_item_cost',
                [ProductionPriceCalculator::class, 'calculateItemCost']
            ),
            new TwigFilter(
                'production_item_total_cost',
                [ProductionPriceCalculator::class, 'calculateItemTotalCost']
            ),
            new TwigFilter(
                'production_order_cost',
                [ProductionPriceCalculator::class, 'calculateOrderCost']
            ),
            new TwigFilter(
                'subject_bill_of_materials',
                [ManufactureHelper::class, 'getBOMBySubject']
            ),
            new TwigFilter(
                'subject_validated_bill_of_material',
                [ManufactureHelper::class, 'getValidatedBOMBySubject']
            ),
            new TwigFilter(
                'subject_bill_of_materials_component',
                [ManufactureHelper::class, 'getBOMsByComponentSubject']
            ),
            new TwigFilter(
                'production_order_can_be_produced',
                [ManufactureHelper::class, 'canOrderBeProduced']
            ),
            new TwigFilter(
                'production_order_can_be_upgraded',
                [ManufactureHelper::class, 'canOrderBeUpgraded']
            ),
            new TwigFilter(
                'production_order_quantity',
                [ManufactureHelper::class, 'renderProductionOrderQuantity'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'production_item_missing_quantity',
                [ManufactureHelper::class, 'renderItemMissingQuantity'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function getTests(): array
    {
        return [
            new TwigTest('draft_bill_of_materials', function (BillOfMaterialsInterface $bom) {
                return $bom->getState() === BOMState::DRAFT;
            }),
            new TwigTest('archived_bill_of_materials', function (BillOfMaterialsInterface $bom) {
                return $bom->getState() === BOMState::ARCHIVED;
            }),
            new TwigTest('validated_bill_of_materials', function (BillOfMaterialsInterface $bom) {
                return $bom->getState() === BOMState::VALIDATED;
            }),
            new TwigTest('new_production_order', function (ProductionOrderInterface $order) {
                return $order->getState() === POState::NEW;
            }),
            new TwigTest('scheduled_production_order', function (ProductionOrderInterface $order) {
                return $order->getState() === POState::SCHEDULED;
            }),
            new TwigTest('done_production_order', function (ProductionOrderInterface $order) {
                return $order->getState() === POState::DONE;
            }),
            new TwigTest('canceled_production_order', function (ProductionOrderInterface $order) {
                return $order->getState() === POState::CANCELED;
            }),
            new TwigTest('stockable_production_order', function (ProductionOrderInterface $order) {
                return POState::isStockableState($order);
            }),
        ];
    }
}
