<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ShipmentParcelType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentParcelType extends ResourceFormType
{
    /**
     * @var string
     */
    private $defaultCurrency;


    /**
     * @inheritDoc
     */
    public function __construct($class, $defaultCurrency)
    {
        parent::__construct($class);

        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('weight', Type\NumberType::class, [
                'label' => 'ekyna_core.field.weight',
                'scale' => 3,
                'attr'  => [
                    'placeholder' => 'ekyna_core.field.weight',
                    'input_group' => ['append' => 'kg'],
                    'min'         => 0,
                ],
            ])
            ->add('valorization', Type\MoneyType::class, [
                'label'    => 'ekyna_commerce.shipment.field.valorization',
                'currency' => $this->defaultCurrency,
                'required' => false,
                'attr'     => [
                    'placeholder' => 'ekyna_commerce.shipment.field.valorization',
                ],
            ])
            ->add('trackingNumber', Type\TextType::class, [
                'label'    => 'ekyna_commerce.shipment.field.tracking_number',
                'attr'     => [
                    'placeholder' => 'ekyna_commerce.shipment.field.tracking_number',
                ],
                'required' => false,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_shipment_parcel';
    }
}
