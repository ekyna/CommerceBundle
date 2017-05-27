<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderStates;
use Ekyna\Bundle\CommerceBundle\Table\Column\SupplierOrderStateType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

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
     * Constructor.
     *
     * @param string $supplierOrderClass
     * @param string $supplierClass
     */
    public function __construct($supplierOrderClass, $supplierClass)
    {
        parent::__construct($supplierOrderClass);

        $this->supplierClass = $supplierClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
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
            ->addColumn('state', SupplierOrderStateType::class, [
                'label'    => 'ekyna_commerce.supplier_order.field.state',
                'position' => 30,
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
                'position'     => 10,
            ])
            ->addFilter('state', CType\Filter\ChoiceType::class, [
                'label'    => 'ekyna_commerce.supplier_order.field.state',
                'choices'  => SupplierOrderStates::getChoices(),
                'position' => 20,
            ]);
    }
}
