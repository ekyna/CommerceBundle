<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Common\Renderer;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;

/**
 * Class CommonExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CommonExtension extends \Twig_Extension
{
    /**
     * @var ConstantsHelper
     */
    private $constantHelper;

    /**
     * @var Renderer
     */
    private $commonRenderer;


    /**
     * Constructor.
     *
     * @param ConstantsHelper $helper
     * @param Renderer $renderer
     */
    public function __construct(ConstantsHelper $helper, Renderer $renderer)
    {
        $this->constantHelper = $helper;
        $this->commonRenderer = $renderer;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'address',
                [$this->commonRenderer, 'renderAddress'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'identity',
                [$this->constantHelper, 'renderIdentity'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'gender',
                [$this->constantHelper, 'getGenderLabel']
            ),
            new \Twig_SimpleFilter(
                'adjustment_mode_label',
                [$this->constantHelper, 'getAdjustmentModeLabel']
            ),
            new \Twig_SimpleFilter(
                'adjustment_type_label',
                [$this->constantHelper, 'getAdjustmentTypeLabel']
            ),
            new \Twig_SimpleFilter(
                'accounting_type_label',
                [$this->constantHelper, 'renderAccountingTypeLabel']
            ),
            new \Twig_SimpleFilter(
                'customer_state_label',
                [$this->constantHelper, 'renderCustomerStateLabel']
            ),
            new \Twig_SimpleFilter(
                'customer_state_badge',
                [$this->constantHelper, 'renderCustomerStateBadge'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'notify_type_label',
                [$this->constantHelper, 'renderNotifyTypeLabel']
            ),
            new \Twig_SimpleFilter(
                'sale_flags',
                [$this->commonRenderer, 'renderSaleFlags'],
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
                'sale_custom_buttons',
                [$this->commonRenderer, 'renderSaleCustomButtons'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
