<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Stock;

use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Symfony\Component\Form\Extension\Core\Type as SF;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class AbstractStockUnitType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStockUnitType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            /*->add('geocode', SF\TextType::class, [
                'label'    => t('field.geocode', [], 'EkynaCommerce'),
                'required' => false,
            ])*/
            ->add('shippedQuantity', SF\NumberType::class, [
                'label'    => t('stock_unit.field.shipped_quantity', [], 'EkynaCommerce'),
                'decimal'  => true,
                'scale'    => 3, // TODO Packaging format
                'disabled' => true,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var StockUnitInterface $stockUnit */
            $stockUnit = $event->getData();
            $form = $event->getForm();

            $disabled = null !== $stockUnit->getSupplierOrderItem();

            $form
                ->add('netPrice', SF\NumberType::class, [
                    'label'    => t('field.buy_net_price', [], 'EkynaCommerce'),
                    'decimal'  => true,
                    'scale'    => 5,
                    'disabled' => $disabled,
                ])
                ->add('estimatedDateOfArrival', SF\DateTimeType::class, [
                    'label'    => t('field.estimated_date_of_arrival', [], 'EkynaCommerce'),
                    'disabled' => $disabled,
                    'required' => false,
                ])
                ->add('orderedQuantity', SF\NumberType::class, [
                    'label'    => t('stock_unit.field.ordered_quantity', [], 'EkynaCommerce'),
                    'decimal'  => true,
                    'scale'    => 3, // TODO Packaging format
                    'disabled' => $disabled,
                ])
                ->add('receivedQuantity', SF\NumberType::class, [
                    'label'    => t('stock_unit.field.received_quantity', [], 'EkynaCommerce'),
                    'decimal'  => true,
                    'scale'    => 3, // TODO Packaging format
                    'disabled' => $disabled,
                ]);
        });
    }
}
