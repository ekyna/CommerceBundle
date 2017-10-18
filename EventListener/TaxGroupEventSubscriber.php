<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\TaxGroupEventSubscriber as BaseSubscriber;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;

/**
 * Class TaxGroupEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxGroupEventSubscriber extends BaseSubscriber
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
                'ekyna_commerce.tax_group.message.cant_be_deleted',
                ResourceMessage::TYPE_ERROR
            ));
        }
    }
}
