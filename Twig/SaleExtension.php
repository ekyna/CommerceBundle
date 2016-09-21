<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\ConstantHelper;
use Ekyna\Component\Commerce\Common\View\ViewBuilder;
use Ekyna\Component\Commerce\Common\View\SaleView;

/**
 * Class SaleExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleExtension extends \Twig_Extension
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
                'sale_state_label',
                [$this->constantHelper, 'renderSaleStateLabel'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'sale_state_badge',
                [$this->constantHelper, 'renderSaleStateBadge'],
                ['is_safe' => ['html']]
            ),
            // Builds the sale view form the sale
            new \Twig_SimpleFilter(
                'sale_view',
                [$this->viewBuilder, 'buildSaleView']
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            // Renders the sale view
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
     * @param SaleView          $view
     * @param string            $template TODO remove as defined in view vars
     *
     * @return string
     */
    public function renderSaleView(\Twig_Environment $env, SaleView $view, $template = 'EkynaCommerceBundle:Common:sale_view.html.twig')
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $env->loadTemplate($template)->renderBlock('sale', ['view' => $view]);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_commerce_sale';
    }
}
