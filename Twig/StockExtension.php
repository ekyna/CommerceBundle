<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\CommerceBundle\Service\Stock\StockRenderer;
use Ekyna\Component\Commerce\Stock\Helper\AvailabilityHelperInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;

/**
 * Class StockExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockExtension extends \Twig_Extension
{
    /**
     * @var ConstantsHelper
     */
    private $constantHelper;

    /**
     * @var StockRenderer
     */
    private $stockRenderer;

    /**
     * @var AvailabilityHelperInterface
     */
    private $availabilityHelper;


    /**
     * Constructor.
     *
     * @param ConstantsHelper             $constantHelper
     * @param StockRenderer               $stockRenderer
     * @param AvailabilityHelperInterface $availabilityHelper
     */
    public function __construct(
        ConstantsHelper $constantHelper,
        StockRenderer $stockRenderer,
        AvailabilityHelperInterface $availabilityHelper
    ) {
        $this->constantHelper = $constantHelper;
        $this->stockRenderer = $stockRenderer;
        $this->availabilityHelper = $availabilityHelper;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'stock_unit_state_label',
                [$this->constantHelper, 'renderStockUnitStateLabel'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'stock_unit_state_badge',
                [$this->constantHelper, 'renderStockUnitStateBadge'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'stock_subject_state_label',
                [$this->constantHelper, 'renderStockSubjectStateLabel'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'stock_subject_state_badge',
                [$this->constantHelper, 'renderStockSubjectStateBadge'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'stock_subject_mode_label',
                [$this->constantHelper, 'renderStockSubjectModeLabel'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'stock_subject_mode_badge',
                [$this->constantHelper, 'renderStockSubjectModeBadge'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'stock_subject_availability',
                [$this->availabilityHelper, 'getAvailabilityMessage'],
                ['is_safe' => ['html']]
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
            $tests[] = new \Twig_SimpleTest('stock_mode_' . $constant, function ($mode) use ($constant) {
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
            new \Twig_SimpleFunction(
                'render_subject_stock_units',
                [$this->stockRenderer, 'renderSubjectStockUnits'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'render_subjects_stock',
                [$this->stockRenderer, 'renderSubjectsStock'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
