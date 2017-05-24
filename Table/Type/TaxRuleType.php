<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class TaxRuleType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxRuleType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('name', 'anchor', [
                'label'                => 'ekyna_core.field.name',
                'route_name'           => 'ekyna_commerce_tax_rule_admin_show',
                'route_parameters_map' => [
                    'taxRuleId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('priority', 'number', [
                'label'    => 'ekyna_core.field.priority',
                'position' => 20,
            ])
            ->addColumn('customer', 'boolean', [
                'label'    => 'ekyna_commerce.tax_rule.field.customer',
                'position' => 30,
            ])
            ->addColumn('business', 'boolean', [
                'label'    => 'ekyna_commerce.tax_rule.field.business',
                'position' => 40,
            ])
            ->addColumn('actions', 'admin_actions', [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_tax_rule_admin_edit',
                        'route_parameters_map' => [
                            'taxRuleId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_tax_rule_admin_remove',
                        'route_parameters_map' => [
                            'taxRuleId' => 'id',
                        ],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('name', 'text', [
                'label'    => 'ekyna_core.field.name',
                'position' => 10,
            ])
            ->addFilter('priority', 'number', [
                'label'    => 'ekyna_core.field.priority',
                'position' => 20,
            ])
            ->addFilter('customer', 'boolean', [
                'label'    => 'ekyna_commerce.tax_rule.field.customer',
                'position' => 30,
            ])
            ->addFilter('business', 'boolean', [
                'label'    => 'ekyna_commerce.tax_rule.field.business',
                'position' => 40,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_tax_rule';
    }
}
