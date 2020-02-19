<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Newsletter\SubscriptionHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class NewsletterExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NewsletterExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'newsletter_subscription',
                [SubscriptionHelper::class, 'render'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
