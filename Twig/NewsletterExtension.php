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
                'newsletter_customer_subscription',
                [SubscriptionHelper::class, 'renderCustomerSubscription'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'newsletter_quick_subscription',
                [SubscriptionHelper::class, 'renderQuickSubscription'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
