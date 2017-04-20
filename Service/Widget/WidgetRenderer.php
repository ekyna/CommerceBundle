<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Widget;

use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

use function array_replace;
use function json_encode;

/**
 * Class WidgetRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Widget
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class WidgetRenderer
{
    private WidgetHelper $helper;
    private Environment  $twig;
    private array        $templates;


    public function __construct(
        WidgetHelper $helper,
        Environment $twig,
        array $templates = []
    ) {
        $this->helper = $helper;
        $this->twig = $twig;

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
     */
    public function renderCustomerWidget(array $options = []): string
    {
        return $this->renderWidget($this->helper->getCustomerWidgetData(), $options);
    }

    /**
     * Renders the customer widget dropdown.
     */
    public function renderCustomerDropdown(): string
    {
        return $this->twig->render($this->templates['customer'], [
            'user' => $this->helper->getUser(),
        ]);
    }

    /**
     * Renders the cart widget.
     */
    public function renderCartWidget(array $options = []): string
    {
        return $this->renderWidget($this->helper->getCartWidgetData(), $options);
    }

    /**
     * Renders the cart widget dropdown.
     */
    public function renderCartDropDown(): string
    {
        $cart = $this->helper->getCart();

        return $this->twig->render($this->templates['cart'], [
            'cart' => $cart,
        ]);
    }

    /**
     * Renders the context widget.
     */
    public function renderContextWidget(array $options = []): string
    {
        return $this->renderWidget($this->helper->getContextWidgetData(), $options);
    }

    /**
     * Renders the cart widget dropdown.
     */
    public function renderContextDropDown(Request $request = null): string
    {
        return $this->twig->render($this->templates['context'], [
            'form' => $this->helper->getContextForm($request)->createView(),
        ]);
    }

    /**
     * Renders the currency widget.
     */
    public function renderCurrencyWidget(array $options = []): string
    {
        $data = $this->helper->getCurrencyWidgetData();

        if (empty($data['currencies'])) {
            return '';
        }

        $data = array_replace([
            'tag'   => 'li',
            'class' => null,
        ], $data, $options);

        return $this->twig->render($this->templates['currency'], $data);
    }

    /**
     * Renders the widget.
     */
    private function renderWidget(array $data, array $options): string
    {
        $data = array_replace([
            'tag'   => 'li',
            'label' => null,
            'title' => null,
            'class' => null,
            'icon'  => null,
            'url'   => null,
            'data'  => null,
        ], $data, $options);

        if (!empty($data['url'])) {
            $data['url'] = json_encode($data['url']);
        }

        if (!empty($data['data'])) {
            $data['data'] = json_encode($data['data']);
        }

        return $this->twig->render($this->templates['widget'], $data);
    }
}
