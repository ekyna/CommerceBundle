<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class NotifyModelType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyModelType extends ResourceTableType
{
    /**
     * @inheritdoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->setFilterable(false)
            ->setExportable(false)
            ->setSortable(false)
            ->addColumn('type', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.type',
                'route_name'           => 'ekyna_commerce_notify_model_admin_show',
                'route_parameters_map' => ['notifyModelId' => 'id'],
                'position'             => 10,
            ])
            /*->addColumn('default', CType\Column\BooleanType::class, [
                'label'                 => 'ekyna_core.field.default',
                'route_name'            => 'ekyna_commerce_notify_model_admin_toggle',
                'route_parameters'      => ['field' => 'default'],
                'route_parameters_map'  => ['notifyModelId' => 'id'],
                'true_class'            => 'label-primary',
                'false_class'           => 'label-default',
                'disable_property_path' => 'default',
                'position'              => 70,
            ])*/
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_notify_model_admin_edit',
                        'route_parameters_map' => ['notifyModelId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                 => 'ekyna_core.button.remove',
                        'class'                 => 'danger',
                        'route_name'            => 'ekyna_commerce_notify_model_admin_remove',
                        'route_parameters_map'  => ['notifyModelId' => 'id'],
                        'permission'            => 'delete',
                        'disable_property_path' => 'default',
                    ],
                ],
            ]);
    }
}
