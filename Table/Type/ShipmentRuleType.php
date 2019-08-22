<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\CommerceBundle\Table\Column\VatDisplayModeType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class ShipmentRuleType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentRuleType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.name',
                'sortable'             => true,
                'route_name'           => 'ekyna_commerce_shipment_rule_admin_show',
                'route_parameters_map' => [
                    'shipmentRuleId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('vatMode', VatDisplayModeType::class, [
                'label'    => 'ekyna_commerce.shipment_rule.field.vat_mode',
                'position' => 20,
            ])
            ->addColumn('methods', DType\Column\EntityType::class, [
                'label'        => 'ekyna_commerce.shipment_method.label.plural',
                'entity_label' => 'name',
                'position'     => 30,
            ])
            ->addColumn('countries', DType\Column\EntityType::class, [
                'label'        => 'ekyna_commerce.country.label.plural',
                'entity_label' => 'name',
                'position'     => 40,
            ])
            ->addColumn('customerGroups', DType\Column\EntityType::class, [
                'label'        => 'ekyna_commerce.customer_group.label.plural',
                'entity_label' => 'name',
                'position'     => 50,
            ])
            ->addColumn('startAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_commerce.shipment_rule.field.start_at',
                'time_format' => 'none',
                'position'    => 60,
            ])
            ->addColumn('endAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_commerce.shipment_rule.field.end_at',
                'time_format' => 'none',
                'position'    => 70,
            ])
            ->addColumn('baseTotal', BType\Column\PriceType::class, [
                'label'    => 'ekyna_commerce.shipment_rule.field.base_total',
                'position' => 80,
            ])
            ->addColumn('netPrice', BType\Column\PriceType::class, [
                'label'    => 'ekyna_commerce.field.net_price',
                'position' => 90,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_shipment_rule_admin_edit',
                        'route_parameters_map' => ['shipmentRuleId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_shipment_rule_admin_remove',
                        'route_parameters_map' => ['shipmentRuleId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ]);
    }
}
