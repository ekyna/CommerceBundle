<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\CustomerAddressEventSubscriber as BaseSubscriber;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;

/**
 * Class CustomerAddressEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerAddressEventSubscriber extends BaseSubscriber
{
    /**
     * @inheritdoc
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        try {
            parent::onPreDelete($event);
        } catch (CommerceExceptionInterface $e) {
            $event->addMessage(new ResourceMessage(
                'ekyna_commerce.customer_address.message.cant_be_deleted',
                ResourceMessage::TYPE_ERROR
            ));
        }
    }
}
