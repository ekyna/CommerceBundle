<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class CustomerType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerType extends ResourceTableType
{
    /**
     * @var string
     */
    private $customerGroupClass;


    /**
     * Constructor.
     *
     * @param string $customerClass
     * @param string $customerGroupClass
     */
    public function __construct($customerClass, $customerGroupClass)
    {
        parent::__construct($customerClass);

        $this->customerGroupClass = $customerGroupClass;
    }

    /**
     * @inheritdoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('id', 'id', [
                'sortable' => true,
            ])
            ->addColumn('number', 'anchor', [
                'label'                => 'ekyna_core.field.number',
                'route_name'           => 'ekyna_commerce_customer_admin_show',
                'route_parameters_map' => ['customerId' => 'id'],
                'position'             => 10,
            ])
            ->addColumn('name', 'text', [
                'label'                => 'ekyna_core.field.name',
                'property_path'        => null,
                'position'             => 20,
            ])
            ->addColumn('company', 'text', [
                'label'    => 'ekyna_core.field.company',
                'sortable' => true,
                'position' => 30,
            ])
            ->addColumn('email', 'text', [
                'label'    => 'ekyna_core.field.email',
                'sortable' => true,
                'position' => 40,
            ])
            ->addColumn('customerGroup', 'anchor', [
                'label'                => 'ekyna_commerce.customer_group.label.singular',
                'property_path'        => 'customerGroup.name',
                'sortable'             => false,
                'route_name'           => 'ekyna_commerce_customer_group_admin_show',
                'route_parameters_map' => ['customerGroupId' => 'customerGroup.id'],
                'position'             => 50,
            ])
            ->addColumn('createdAt', 'datetime', [
                'label'    => 'ekyna_core.field.created_at',
                'sortable' => true,
                'position' => 60,
            ])
            ->addColumn('actions', 'admin_actions', [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_customer_admin_edit',
                        'route_parameters_map' => ['customerId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_customer_admin_remove',
                        'route_parameters_map' => ['customerId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('id', 'number')
            ->addFilter('number', 'text', [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addFilter('firstName', 'text', [
                'label'    => 'ekyna_core.field.first_name',
                'position' => 20,
            ])
            ->addFilter('lastName', 'text', [
                'label'    => 'ekyna_core.field.last_name',
                'position' => 25,
            ])
            ->addFilter('company', 'text', [
                'label'    => 'ekyna_core.field.company',
                'position' => 30,
            ])
            ->addFilter('email', 'text', [
                'label'    => 'ekyna_core.field.email',
                'position' => 40,
            ])
            ->addFilter('customerGroup', 'entity', [
                'label'    => 'ekyna_core.field.group',
                'class'    => $this->customerGroupClass,
                'property' => 'name',
                'position' => 50,
            ])
            ->addFilter('createdAt', 'datetime', [
                'label'    => 'ekyna_core.field.created_at',
                'position' => 60,
            ]);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_commerce_customer';
    }
}
