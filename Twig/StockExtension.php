<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\CommerceBundle\Service\Stock\StockRenderer;

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
     * Constructor.
     *
     * @param ConstantsHelper $constantHelper
     * @param StockRenderer   $stockRenderer
     */
    public function __construct(ConstantsHelper $constantHelper, StockRenderer $stockRenderer)
    {
        $this->constantHelper = $constantHelper;
        $this->stockRenderer = $stockRenderer;
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
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'render_stock_unit_list',
                [$this->stockRenderer, 'renderSubjectStockUnitList'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_commerce_stock';
    }
}
