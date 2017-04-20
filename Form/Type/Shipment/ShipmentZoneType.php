<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ShipmentZoneType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentZoneType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label' => t('field.name', [], 'EkynaUi'),
            ])
            ->add('countries', CountryChoiceType::class, [
                'label'    => t('country.label.plural', [], 'EkynaCommerce'),
                'enabled'  => false,
                'multiple' => true,
            ])
            ->add('pricing', ShipmentPricingType::class, [
                'filter_by' => 'method',
            ]);
    }
}
