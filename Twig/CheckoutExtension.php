<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Payment\CheckoutRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class CheckoutExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CheckoutExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'cart_checkout_content',
                [CheckoutRenderer::class, 'renderCheckoutContent'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
