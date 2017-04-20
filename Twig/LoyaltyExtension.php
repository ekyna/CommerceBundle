<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Customer\LoyaltyRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class LoyaltyExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LoyaltyExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'customer_loyalty_logs',
                [LoyaltyRenderer::class, 'renderLogs'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'customer_coupons',
                [LoyaltyRenderer::class, 'renderCoupons'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
