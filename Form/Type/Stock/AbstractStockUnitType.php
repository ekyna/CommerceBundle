<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Stock;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Symfony\Component\Form\Extension\Core\Type as SF;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class AbstractStockUnitType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStockUnitType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // TODO disable fields if linked to a supplier order item.

        $builder
            ->add('netPrice', SF\NumberType::class, [
                'label'    => 'ekyna_commerce.stock_unit.field.net_price',
                'required' => false,
            ])
            ->add('geocode', SF\TextType::class, [
                'label'    => 'ekyna_commerce.stock_unit.field.geocode',
                'required' => false,
            ])
            ->add('estimatedDateOfArrival', SF\DateTimeType::class, [
                'label'    => 'ekyna_commerce.stock_unit.field.estimated_date_of_arrival',
//                'format'         => 'dd/MM/yyyy',
                'required' => false,
            ])
            ->add('orderedQuantity', SF\NumberType::class, [
                'label'    => 'ekyna_commerce.stock_unit.field.ordered_quantity',
                'required' => false,
            ])
            ->add('deliveredQuantity', SF\NumberType::class, [
                'label'    => 'ekyna_commerce.stock_unit.field.delivered_quantity',
                'disabled' => true,
                'required' => false,
            ])
            ->add('shippedQuantity', SF\NumberType::class, [
                'label'    => 'ekyna_commerce.stock_unit.field.shipped_quantity',
                'disabled' => true,
                'required' => false,
            ]);
    }
}
