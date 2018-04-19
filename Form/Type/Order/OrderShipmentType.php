<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Order;

use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OrderShipmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentType extends ShipmentType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'item_type'   => OrderShipmentItemType::class,
            'parcel_type' => OrderShipmentParcelType::class,
        ]);
    }
}
