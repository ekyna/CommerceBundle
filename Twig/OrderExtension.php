<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Helper\ConstantHelper;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\View\Builder as ViewBuilder;

/**
 * Class OrderExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderExtension extends \Twig_Extension
{
    /**
     * @var ConstantHelper
     */
    private $constantHelper;

    /**
     * @var ViewBuilder
     */
    private $viewBuilder;

    /**
     * @var \Twig_Template
     */
    private $saleViewTemplate;


    /**
     * Constructor.
     *
     * @param ConstantHelper $constantHelper
     * @param ViewBuilder $viewBuilder
     */
    public function __construct(ConstantHelper $constantHelper, ViewBuilder $viewBuilder)
    {
        $this->constantHelper = $constantHelper;
        $this->viewBuilder = $viewBuilder;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'order_state_label',
                [$this->constantHelper, 'renderOrderStateLabel'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'order_state_badge',
                [$this->constantHelper, 'renderOrderStateBadge'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'sale_view',
                [$this->viewBuilder, 'buildSaleView'],
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
                'render_sale_view',
                [$this, 'renderSaleView'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
        ];
    }

    /**
     * Renders the sale view.
     *
     * @param \Twig_Environment $env
     * @param SaleInterface     $sale
     *
     * @return string
     */
    public function renderSaleView(\Twig_Environment $env, SaleInterface $sale, $template = 'EkynaCommerceBundle:Common:sale_view.html.twig')
    {
        $this->saleViewTemplate = $env->loadTemplate($template);

        $view = $this->viewBuilder->buildSaleView($sale);

        return $this->saleViewTemplate->renderBlock('sale', ['view' => $view]);
    }


    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_commerce_order';
    }
}
