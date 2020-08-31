<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Newsletter\Model\SubscriptionStatus as State;

/**
 * Class SubscriptionState
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class SubscriptionStatus extends AbstractConstants
{
    /**
     * @inheritDoc
     */
    public static function getConfig(): array
    {
        $prefix = 'ekyna_commerce.subscription.status.';

        return [
            State::SUBSCRIBED   => [$prefix . State::SUBSCRIBED,   'success'],
            State::UNSUBSCRIBED => [$prefix . State::UNSUBSCRIBED, 'default'],
        ];
    }
}
