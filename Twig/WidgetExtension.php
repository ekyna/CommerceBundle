<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Widget\WidgetRenderer;
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
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'commerce_customer_widget',
                [WidgetRenderer::class, 'renderCustomerWidget'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'commerce_cart_widget',
                [WidgetRenderer::class, 'renderCartWidget'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'commerce_context_widget',
                [WidgetRenderer::class, 'renderContextWidget'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'commerce_currency_widget',
                [WidgetRenderer::class, 'renderCurrencyWidget'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
