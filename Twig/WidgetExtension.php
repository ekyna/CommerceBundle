<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Widget\WidgetHelper;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class WidgetExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WidgetExtension extends AbstractExtension
{
    /**
     * @var WidgetHelper
     */
    private $widgetHelper;

    /**
     * @var array
     */
    private $config;


    /**
     * Constructor.
     *
     * @param WidgetHelper $widgetHelper
     * @param array        $config
     */
    public function __construct(WidgetHelper $widgetHelper, array $config = [])
    {
        $this->widgetHelper = $widgetHelper;

        $this->config = array_replace([
            'widget_template'   => '@EkynaCommerce/Js/widget.html.twig',
            'currency_template' => '@EkynaCommerce/Widget/currency.html.twig',
        ], $config);
    }

    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'commerce_customer_widget',
                [$this, 'renderCustomerWidget'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'commerce_cart_widget',
                [$this, 'renderCartWidget'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'commerce_context_widget',
                [$this, 'renderContextWidget'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'commerce_currency_widget',
                [$this, 'renderCurrencyWidget'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
        ];
    }

    /**
     * Renders the customer widget.
     *
     * @param Environment $env
     * @param array       $options
     *
     * @return string
     */
    public function renderCustomerWidget(Environment $env, array $options = [])
    {
        return $this->renderWidget($env, $this->widgetHelper->getCustomerWidgetData(), $options);
    }

    /**
     * Renders the cart widget.
     *
     * @param Environment $env
     * @param array       $options
     *
     * @return string
     */
    public function renderCartWidget(Environment $env, array $options = [])
    {
        return $this->renderWidget($env, $this->widgetHelper->getCartWidgetData(), $options);
    }

    /**
     * Renders the context widget.
     *
     * @param Environment $env
     * @param array       $options
     *
     * @return string
     */
    public function renderContextWidget(Environment $env, array $options = [])
    {
        return $this->renderWidget($env, $this->widgetHelper->getContextWidgetData(), $options);
    }

    /**
     * Renders the currency widget.
     *
     * @param Environment $env
     * @param array       $options
     *
     * @return string
     */
    public function renderCurrencyWidget(Environment $env, array $options = [])
    {
        $data = $this->widgetHelper->getCurrencyWidgetData();

        if (empty($data['currencies'])) {
            return '';
        }

        $data = array_replace([
            'template' => $this->config['currency_template'],
            'tag'      => 'li',
            'class'    => null,
        ], $data, $options);

        return $env->render($data['template'], $data);
    }

    /**
     * Renders the widget.
     *
     * @param Environment $env
     * @param array       $data
     * @param array       $options
     *
     * @return string
     */
    private function renderWidget(Environment $env, array $data, array $options)
    {
        $data = array_replace([
            'template' => $this->config['widget_template'],
            'tag'      => 'li',
            'class'    => null,
            'icon'     => null,
            'url'      => null,
            'data'     => null,
        ], $data, $options);

        if (!empty($data['url'])) {
            $data['url'] = \json_encode($data['url']);
        }

        if (!empty($data['data'])) {
            $data['data'] = \json_encode($data['data']);
        }

        return $env->render($data['template'], $data);
    }
}
