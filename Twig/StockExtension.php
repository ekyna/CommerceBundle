<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\CommerceBundle\Service\Stock\StockRenderer;
use Ekyna\Component\Commerce\Stock\Helper\AvailabilityHelperInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Prioritizer\StockPrioritizerInterface;
use Ekyna\Component\Commerce\Stock\Provider\WarehouseProviderInterface;
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
    /**
     * @var StockRenderer
     */
    private $stockRenderer;

    /**
     * @var AvailabilityHelperInterface
     */
    private $availabilityHelper;

    /**
     * @var StockPrioritizerInterface
     */
    private $stockPrioritizer;

    /**
     * @var WarehouseProviderInterface
     */
    private $warehouseProvider;


    /**
     * Constructor.
     *
     * @param StockRenderer               $stockRenderer
     * @param AvailabilityHelperInterface $availabilityHelper
     * @param StockPrioritizerInterface   $stockPrioritizer
     * @param WarehouseProviderInterface  $warehouseProvider
     */
    public function __construct(
        StockRenderer $stockRenderer,
        AvailabilityHelperInterface $availabilityHelper,
        StockPrioritizerInterface $stockPrioritizer,
        WarehouseProviderInterface $warehouseProvider
    ) {
        $this->stockRenderer = $stockRenderer;
        $this->availabilityHelper = $availabilityHelper;
        $this->stockPrioritizer = $stockPrioritizer;
        $this->warehouseProvider = $warehouseProvider;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
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
                [$this->availabilityHelper, 'getAvailabilityMessage'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'stock_can_prioritize',
                [$this->stockPrioritizer, 'canPrioritizeSale']
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getTests()
    {
        $tests = [];

        foreach (StockSubjectModes::getModes() as $constant) {
            $tests[] = new TwigTest('stock_mode_' . $constant, function ($mode) use ($constant) {
                return $mode === $constant;
            });
        }

        return $tests;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'render_subject_stock_units',
                [$this->stockRenderer, 'renderSubjectStockUnits'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'render_subjects_stock',
                [$this->stockRenderer, 'renderSubjectsStock'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'commerce_warehouse',
                [$this->warehouseProvider, 'getWarehouse']
            ),
        ];
    }
}
