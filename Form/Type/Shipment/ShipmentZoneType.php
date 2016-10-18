<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ShipmentZoneType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentZoneType extends ResourceFormType
{
    /**
     * @var string
     */
    private $countryClass;

    /**
     * @var string
     */
    private $methodClass;


    /**
     * Constructor.
     *
     * @param string $dataClass
     * @param string $countryClass
     */
    public function __construct($dataClass, $countryClass, $methodClass)
    {
        parent::__construct($dataClass);

        $this->countryClass = $countryClass;
        $this->methodClass = $methodClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label' => 'ekyna_core.field.name',
            ])
            ->add('countries', EntityType::class, [
                'label'    => 'ekyna_commerce.country.label.plural',
                'class'    => $this->countryClass,
                'multiple' => true,
            ])
            ->add('method', EntityType::class, [
                'label'    => 'ekyna_commerce.shipment_method.label.singular',
                'class'    => $this->methodClass,
                'mapped' => false,
                'attr' => [
                    'class' => 'commerce-shipment-zone-method no-select2',
                ]
            ])
            ->add('prices', ShipmentPricesType::class, [
                'attr' => [
                    'class' => 'commerce-shipment-zone-prices'
                ]
            ]);
    }
}
