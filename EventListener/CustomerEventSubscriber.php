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
            CustomerEvents::PRE_CREATE     => ['onPreCreate', 0],
            CustomerEvents::PRE_UPDATE     => ['onPreUpdate', 0],
            CustomerEvents::PRE_DELETE     => ['onPreDelete', 0],
        ];
    }
}
