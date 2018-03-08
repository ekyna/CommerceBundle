<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Widget\WidgetHelper;

/**
 * Class WidgetExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WidgetExtension extends \Twig_Extension implements \Twig_Extension_InitRuntimeInterface
{
    /**
     * @var WidgetHelper
     */
    private $widgetHelper;

    /**
     * @var \Twig_Environment
     */
    private $environment;

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
    public function __construct(
        WidgetHelper $widgetHelper,
        array $config = []
    ) {
        $this->widgetHelper = $widgetHelper;

        $this->config = array_replace([
            'template' => 'EkynaCommerceBundle:Js:widget.html.twig',
        ], $config);
    }

    /**
     * @inheritDoc
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'commerce_customer_widget',
                [$this, 'renderCustomerWidget'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'commerce_cart_widget',
                [$this, 'renderCartWidget'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Renders the customer widget.
     *
     * @param array $options
     *
     * @return string
     */
    public function renderCustomerWidget(array $options = [])
    {
        return $this->renderWidget($this->widgetHelper->getCustomerWidgetData(), $options);
    }

    /**
     * Renders the cart widget.
     *
     * @param array $options
     *
     * @return string
     */
    public function renderCartWidget(array $options = [])
    {
        return $this->renderWidget($this->widgetHelper->getCartWidgetData(), $options);
    }

    /**
     * Renders the widget.
     *
     * @param array $data
     * @param array $options
     *
     * @return string
     */
    private function renderWidget(array $data, array $options)
    {
        $options = array_replace([
            'template' => $this->config['template'],
            'tag'      => 'li',
            'class'    => '',
        ], $options);

        $data = array_replace([
            'tag'   => $options['tag'],
            'class' => $options['class'],
        ], $data);

        return $this->environment->render($options['template'], $data);
    }
}