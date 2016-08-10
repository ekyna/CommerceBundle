<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Component\Commerce\Customer\Event\CustomerEvents;
use Ekyna\Component\Commerce\Customer\EventListener\CustomerListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CustomerEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerEventSubscriber extends CustomerListener implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CustomerEvents::INSERT  => ['onInsert', 0],
            CustomerEvents::UPDATE  => ['onUpdate', 0],
            CustomerEvents::DELETE  => ['onDelete', 0],
        ];
    }
}
