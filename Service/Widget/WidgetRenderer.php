<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Widget;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class WidgetRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Widget
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WidgetRenderer
{
    /**
     * @var WidgetHelper
     */
    private $helper;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var array
     */
    private $templates;


    /**
     * Constructor.
     *
     * @param WidgetHelper    $helper
     * @param EngineInterface $templating
     * @param array           $templates
     */
    public function __construct(
        WidgetHelper $helper,
        EngineInterface $templating,
        array $templates = []
    ) {
        $this->helper     = $helper;
        $this->templating = $templating;

        $this->templates = array_replace([
            'widget'   => '@EkynaCommerce/Js/widget.html.twig',
            'cart'     => '@EkynaCommerce/Widget/cart.html.twig',
            'context'  => '@EkynaCommerce/Widget/context.html.twig',
            'currency' => '@EkynaCommerce/Widget/currency.html.twig',
            'customer' => '@EkynaCommerce/Widget/customer.html.twig',
        ], $templates);
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
        return $this->renderWidget($this->helper->getCustomerWidgetData(), $options);
    }

    /**
     * Renders the customer widget dropdown.
     *
     * @return string
     */
    public function renderCustomerDropdown()
    {
        return $this->templating->render($this->templates['customer'], [
            'user' => $this->helper->getUser(),
        ]);
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
        return $this->renderWidget($this->helper->getCartWidgetData(), $options);
    }

    /**
     * Renders the cart widget dropdown.
     *
     * @return string
     */
    public function renderCartDropDown(): string
    {
        $cart = $this->helper->getCart();

        return $this->templating->render($this->templates['cart'], [
            'cart' => $cart,
        ]);
    }

    /**
     * Renders the context widget.
     *
     * @param array $options
     *
     * @return string
     */
    public function renderContextWidget(array $options = [])
    {
        return $this->renderWidget($this->helper->getContextWidgetData(), $options);
    }

    /**
     * Renders the cart widget dropdown.
     *
     * @param Request|null $request
     *
     * @return string
     */
    public function renderContextDropDown(Request $request = null): string
    {
        return $this->templating->render($this->templates['context'], [
            'form' => $this->helper->getContextForm($request)->createView(),
        ]);
    }

    /**
     * Renders the currency widget.
     *
     * @param array $options
     *
     * @return string
     */
    public function renderCurrencyWidget(array $options = [])
    {
        $data = $this->helper->getCurrencyWidgetData();

        if (empty($data['currencies'])) {
            return '';
        }

        $data = array_replace([
            'tag'      => 'li',
            'class'    => null,
        ], $data, $options);

        return $this->templating->render($this->templates['currency'], $data);
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
        $data = array_replace([
            'tag'      => 'li',
            'label'    => null,
            'title'    => null,
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

        return $this->templating->render($this->templates['widget'], $data);
    }
}
