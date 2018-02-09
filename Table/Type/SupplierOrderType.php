<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderStates;
use Ekyna\Bundle\CommerceBundle\Table\Column\SupplierOrderPaymentType;
use Ekyna\Bundle\CommerceBundle\Table\Column\SupplierOrderStateType;
use Ekyna\Bundle\CommerceBundle\Table\Column\SupplierOrderTrackingType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Ekyna\Component\Table\View\RowView;

/**
 * Class SupplierOrderType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderType extends ResourceTableType
{
    /**
     * @var string
     */
    private $supplierClass;

    /**
     * @var string
     */
    private $carrierClass;


    /**
     * Constructor.
     *
     * @param string $supplierOrderClass
     * @param string $supplierClass
     * @param string $carrierClass
     */
    public function __construct($supplierOrderClass, $supplierClass, $carrierClass)
    {
        parent::__construct($supplierOrderClass);

        $this->supplierClass = $supplierClass;
        $this->carrierClass = $carrierClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addDefaultSort('number', ColumnSort::DESC)
            ->addColumn('number', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.number',
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_supplier_order_admin_show',
                'route_parameters_map' => [
                    'supplierOrderId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('supplier', DType\Column\EntityType::class, [
                'label'                => 'ekyna_commerce.supplier.label.singular',
                'entity_label'         => 'name',
                'route_name'           => 'ekyna_commerce_supplier_admin_show',
                'route_parameters_map' => [
                    'supplierId' => 'id',
                ],
                'position'             => 20,
            ])
            ->addColumn('carrier', DType\Column\EntityType::class, [
                'label'                => 'ekyna_commerce.supplier_carrier.label.singular',
                'entity_label'         => 'name',
                'route_name'           => 'ekyna_commerce_supplier_carrier_admin_show',
                'route_parameters_map' => [
                    'supplierCarrierId' => 'id',
                ],
                'position'             => 30,
            ])
            ->addColumn('state', SupplierOrderStateType::class, [
                'label'    => 'ekyna_commerce.supplier_order.field.state',
                'position' => 30,
            ])
            ->addColumn('estimatedDateOfArrival', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_commerce.supplier_order.field.estimated_date_of_arrival',
                'time_format' => 'none',
                'position'    => 40,
            ])
            ->addColumn('trackingUrls', SupplierOrderTrackingType::class, [
                'label'    => 'ekyna_commerce.supplier_order.field.tracking_urls',
                'position' => 50,
            ])
            ->addColumn('paymentDate', SupplierOrderPaymentType::class, [
                'label'    => 'ekyna_commerce.supplier_order.field.payment_date',
                'prefix'   => 'payment',
                'position' => 60,
            ])
            ->addColumn('forwarderDate', SupplierOrderPaymentType::class, [
                'label'    => 'ekyna_commerce.supplier_order.field.forwarder_date',
                'prefix'   => 'forwarder',
                'position' => 70,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_supplier_order_admin_edit',
                        'route_parameters_map' => [
                            'supplierOrderId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_supplier_order_admin_remove',
                        'route_parameters_map' => [
                            'supplierOrderId' => 'id',
                        ],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addFilter('supplier', DType\Filter\EntityType::class, [
                'label'        => 'ekyna_commerce.supplier.label.singular',
                'class'        => $this->supplierClass,
                'entity_label' => 'name',
                'position'     => 20,
            ])
            ->addFilter('carrier', DType\Filter\EntityType::class, [
                'label'        => 'ekyna_commerce.supplier_carrier.label.singular',
                'class'        => $this->carrierClass,
                'entity_label' => 'name',
                'position'     => 30,
            ])
            ->addFilter('state', CType\Filter\ChoiceType::class, [
                'label'    => 'ekyna_commerce.supplier_order.field.state',
                'choices'  => SupplierOrderStates::getChoices(),
                'position' => 40,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function buildRowView(RowView $view, RowInterface $row, array $options)
    {
        $view->vars['attr']['data-summary'] = json_encode([
            'route'      => 'ekyna_commerce_supplier_order_admin_summary',
            'parameters' => ['supplierOrderId' => $row->getData('id')],
        ]);
    }
}
