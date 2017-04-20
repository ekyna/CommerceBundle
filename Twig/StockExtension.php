<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\CommerceBundle\Service\Stock\AvailabilityHelper;
use Ekyna\Bundle\CommerceBundle\Service\Stock\StockRenderer;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Prioritizer\StockPrioritizer;
use Ekyna\Component\Commerce\Stock\Provider\WarehouseProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Class StockExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'stock_unit_state_label',
                [ConstantsHelper::class, 'renderStockUnitStateLabel'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'stock_unit_state_badge',
                [ConstantsHelper::class, 'renderStockUnitStateBadge'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'stock_subject_state_label',
                [ConstantsHelper::class, 'renderStockSubjectStateLabel'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'stock_subject_state_badge',
                [ConstantsHelper::class, 'renderStockSubjectStateBadge'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'stock_subject_mode_label',
                [ConstantsHelper::class, 'renderStockSubjectModeLabel'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'stock_subject_mode_badge',
                [ConstantsHelper::class, 'renderStockSubjectModeBadge'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'stock_subject_availability',
                [AvailabilityHelper::class, 'getAvailabilityMessage'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'stock_can_prioritize',
                [StockPrioritizer::class, 'canPrioritizeSale']
            ),
        ];
    }

    public function getTests(): array
    {
        $tests = [];

        foreach (StockSubjectModes::getModes() as $constant) {
            $tests[] = new TwigTest('stock_mode_' . $constant, function ($mode) use ($constant) {
                return $mode === $constant;
            });
        }

        return $tests;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'render_subject_stock_units',
                [StockRenderer::class, 'renderSubjectStockUnits'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'render_subjects_stock',
                [StockRenderer::class, 'renderSubjectsStock'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'commerce_warehouse',
                [WarehouseProvider::class, 'getWarehouse']
            ),
        ];
    }
}
