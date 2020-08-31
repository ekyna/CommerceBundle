<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\Column\ConstantChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\SubscriptionStatus;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\AbstractTableType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class SubscriptionType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionType extends AbstractTableType
{
    /**
     * @inheritDoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->setSortable(false)
            ->setBatchable(false)
            ->setConfigurable(false)
            ->setExportable(false)
            ->setFilterable(false)
            ->setProfileable(false)
            ->addColumn('audience', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_commerce.audience.label.plural',
                'route_name'           => 'ekyna_commerce_audience_admin_show',
                'route_parameters_map' => ['audienceId' => 'audience.id'],
                'position'             => 20,
            ])
            ->addColumn('status', ConstantChoiceType::class, [
                'label'    => 'ekyna_core.field.status',
                'class'    => SubscriptionStatus::class,
                'theme'    => true,
                'position' => 30,
            ])
            /*->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'icon'                 => 'pencil',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_member_admin_edit',
                        'route_parameters_map' => [
                            'memberId' => 'member.id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'icon'                 => 'trash',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_member_admin_remove',
                        'route_parameters_map' => [
                            'memberId' => 'id',
                        ],
                        'permission'           => 'member.delete',
                    ],
                ],
            ])*/;
    }
}
