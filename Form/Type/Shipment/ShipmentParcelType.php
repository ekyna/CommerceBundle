<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ShipmentParcelType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentParcelType extends AbstractResourceType
{
    private string $defaultCurrency;

    public function __construct(string $defaultCurrency)
    {
        $this->defaultCurrency = $defaultCurrency;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('weight', Type\NumberType::class, [
                'label'   => t('field.weight', [], 'EkynaUi'),
                'decimal' => true,
                'scale'   => 3,
                'attr'    => [
                    'placeholder' => t('field.weight', [], 'EkynaUi'),
                    'input_group' => ['append' => 'kg'],
                    'min'         => 0,
                ],
            ])
            ->add('valorization', Type\MoneyType::class, [
                'label'    => t('shipment.field.valorization', [], 'EkynaCommerce'),
                'decimal'  => true,
                'currency' => $this->defaultCurrency,
                'required' => false,
                'attr'     => [
                    'placeholder' => t('shipment.field.valorization', [], 'EkynaCommerce'),
                ],
            ])
            ->add('trackingNumber', Type\TextType::class, [
                'label'    => t('shipment.field.tracking_number', [], 'EkynaCommerce'),
                'attr'     => [
                    'placeholder' => t('shipment.field.tracking_number', [], 'EkynaCommerce'),
                ],
                'required' => false,
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_shipment_parcel';
    }
}
