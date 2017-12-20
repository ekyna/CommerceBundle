<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Stock;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Symfony\Component\Form\Extension\Core\Type as SF;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
        $builder
            /*->add('geocode', SF\TextType::class, [
                'label'    => 'ekyna_commerce.stock_unit.field.geocode',
                'required' => false,
            ])*/
            ->add('shippedQuantity', SF\NumberType::class, [
                'label'    => 'ekyna_commerce.stock_unit.field.shipped_quantity',
                'disabled' => true,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            /** @var \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface $stockUnit */
            $stockUnit = $event->getData();
            $form = $event->getForm();

            $disabled = null !== $stockUnit->getSupplierOrderItem();

            $form
                ->add('netPrice', SF\NumberType::class, [
                    'label'    => 'ekyna_commerce.stock_unit.field.net_price',
                    'disabled' => $disabled,
                ])
                ->add('estimatedDateOfArrival', SF\DateTimeType::class, [
                    'label'    => 'ekyna_commerce.stock_unit.field.estimated_date_of_arrival',
//                'format'         => 'dd/MM/yyyy',
                    'disabled' => $disabled,
                    'required' => false,
                ])
                ->add('orderedQuantity', SF\NumberType::class, [
                    'label'    => 'ekyna_commerce.stock_unit.field.ordered_quantity',
                    'disabled' => $disabled,
                ])
                ->add('receivedQuantity', SF\NumberType::class, [
                    'label'    => 'ekyna_commerce.stock_unit.field.received_quantity',
                    'disabled' => $disabled,
                ]);
        });
    }
}
