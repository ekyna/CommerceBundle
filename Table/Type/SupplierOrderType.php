<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\AdminBundle\Table\Type\Filter\ConstantChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderStates;
use Ekyna\Bundle\CommerceBundle\Table\Column;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class SupplierOrderType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addDefaultSort('number', ColumnSort::DESC)
            ->addColumn('number', BType\Column\AnchorType::class, [
                'label'    => t('field.number', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => t('field.created_at', [], 'EkynaUi'),
                'time_format' => 'none',
                'position'    => 20,
            ])
            ->addColumn('supplier', DType\Column\EntityType::class, [
                'label'    => t('supplier.label.singular', [], 'EkynaCommerce'),
                'position' => 30,
            ])
            ->addColumn('carrier', DType\Column\EntityType::class, [
                'label'    => t('supplier_carrier.label.singular', [], 'EkynaCommerce'),
                'position' => 40,
            ])
            ->addColumn('state', Column\SupplierOrderStateType::class, [
                'label'    => t('field.status', [], 'EkynaCommerce'),
                'position' => 50,
            ])
            ->addColumn('estimatedDateOfArrival', CType\Column\DateTimeType::class, [
                'label'       => t('field.estimated_date_of_arrival', [], 'EkynaCommerce'),
                'time_format' => 'none',
                'position'    => 60,
            ])
            ->addColumn('trackingUrls', Column\SupplierOrderTrackingType::class, [
                'label'    => t('supplier_order.field.tracking_urls', [], 'EkynaCommerce'),
                'position' => 70,
            ])
            ->addColumn('paymentTotal', BType\Column\PriceType::class, [
                'label'         => t('supplier_order.field.payment_total', [], 'EkynaCommerce'),
                'currency_path' => 'currency.code',
                'position'      => 80,
            ])
            ->addColumn('paymentDate', Column\SupplierOrderPaymentType::class, [
                'label'    => t('supplier_order.field.payment_date', [], 'EkynaCommerce'),
                'prefix'   => 'payment',
                'position' => 90,
            ])
            ->addColumn('forwarderTotal', BType\Column\PriceType::class, [
                'label'         => t('supplier_order.field.forwarder_total', [], 'EkynaCommerce'),
                'currency_path' => 'currency.code',
                'position'      => 100,
            ])
            ->addColumn('forwarderDate', Column\SupplierOrderPaymentType::class, [
                'label'    => t('supplier_order.field.forwarder_date', [], 'EkynaCommerce'),
                'prefix'   => 'forwarder',
                'position' => 110,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    UpdateAction::class,
                    DeleteAction::class,
                ],
            ])
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => t('field.number', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                'label'    => t('field.date', [], 'EkynaUi'),
                'position' => 20,
                'time'     => false,
            ])
            ->addFilter('supplier', ResourceType::class, [
                'resource' => 'ekyna_commerce.supplier',
                'position' => 30,
            ])
            ->addFilter('carrier', ResourceType::class, [
                'resource' => 'ekyna_commerce.supplier_carrier',
                'position' => 40,
            ])
            ->addFilter('state', ConstantChoiceType::class, [
                'label'    => t('field.status', [], 'EkynaCommerce'),
                'class'    => SupplierOrderStates::class,
                'position' => 50,
            ])
            ->addFilter('paymentDate', CType\Filter\DateTimeType::class, [
                'label'    => t('supplier_order.field.payment_date', [], 'EkynaCommerce'),
                'position' => 60,
                'time'     => false,
            ])
            ->addFilter('paymentDueDate', CType\Filter\DateTimeType::class, [
                'label'    => t('supplier_order.field.payment_due_date', [], 'EkynaCommerce'),
                'position' => 70,
                'time'     => false,
            ])
            ->addFilter('forwarderDate', CType\Filter\DateTimeType::class, [
                'label'    => t('supplier_order.field.forwarder_date', [], 'EkynaCommerce'),
                'position' => 80,
                'time'     => false,
            ])
            ->addFilter('forwarderDueDate', CType\Filter\DateTimeType::class, [
                'label'    => t('supplier_order.field.forwarder_due_date', [], 'EkynaCommerce'),
                'position' => 90,
                'time'     => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('resource_summary', true);
    }
}
