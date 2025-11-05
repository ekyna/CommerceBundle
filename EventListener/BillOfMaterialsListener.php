<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Manufacture\EventListener\BillOfMaterialsListener as BaseListener;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;

/**
 * Class BillOfMaterialsListener
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BillOfMaterialsListener extends BaseListener
{
    public function onPreDelete(ResourceEventInterface $event): void
    {
        try {
            parent::onPreDelete($event);
        } catch (CommerceExceptionInterface) {
            $event->addMessage(ResourceMessage::create(
                'bill_of_material.message.cant_be_deleted',
                ResourceMessage::TYPE_ERROR
            )->setDomain('EkynaCommerce'));
        }
    }
}
