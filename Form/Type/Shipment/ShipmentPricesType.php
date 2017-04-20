<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShipmentPricesType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPricesType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
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
            ]);
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_shipment_prices';
    }
}
