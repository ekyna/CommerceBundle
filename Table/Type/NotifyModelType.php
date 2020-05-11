<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\CommerceBundle\Table\Column\NotifyModelTypeType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Extension\Core\Type as CType;

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
            ->addColumn('type', NotifyModelTypeType::class, [
                'position'             => 10,
            ])
            ->addColumn('enabled', CType\Column\BooleanType::class, [
                'label'                 => 'ekyna_core.field.enabled',
                'route_name'            => 'ekyna_commerce_notify_model_admin_toggle',
                'route_parameters'      => ['field' => 'enabled'],
                'route_parameters_map'  => ['notifyModelId' => 'id'],
                'position'              => 70,
            ])
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
                    ],
                ],
            ]);
    }
}
