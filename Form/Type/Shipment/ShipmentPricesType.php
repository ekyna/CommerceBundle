<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShipmentPricesType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPricesType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('filter_by')
            ->setAllowedValues('filter_by', ['zone', 'method'])
            ->setDefaults([
                'label'         => false,
                'entry_type'    => ShipmentPriceType::class,
                'entry_options' => function (Options $options) {
                    return [
                        'filter_by' => $options['filter_by'],
                    ];
                }
//                'add_button_text'       => 'ekyna_commerce.supplier_order.button.add_item',
//                'delete_button_confirm' => 'ekyna_commerce.supplier_order.message.confirm_item_removal',
            ]);
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_shipment_prices';
    }
}
