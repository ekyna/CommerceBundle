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
     * Constructor.
     *
     * @param string $dataClass
     * @param string $countryClass
     */
    public function __construct($dataClass, $countryClass)
    {
        parent::__construct($dataClass);

        $this->countryClass = $countryClass;
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
            ->add('pricing', ShipmentPricingType::class, [
                'filter_by' => 'method',
            ]);
    }
}
