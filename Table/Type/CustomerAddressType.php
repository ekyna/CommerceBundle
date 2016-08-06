<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class CustomerAddressType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerAddressType extends ResourceTableType
{
    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addColumn('id', 'number', [
//                'sortable' => true,
            ])
            ->addColumn('street', 'text', [
                'label'                => 'ekyna_core.field.name',
                'property_path'        => null,
//                'sortable'             => true,
//                'route_name'           => 'ekyna_commerce_customer_admin_show',
//                'route_parameters_map' => ['customerId' => 'id'],
            ])
            /*->addColumn('email', 'text', array(
                'label' => 'ekyna_core.field.email',
                'sortable' => true,
            ))
            ->addColumn('groups', 'anchor', [
                'label'                => 'ekyna_core.field.group',
                'property_path'        => 'group.name',
                'sortable'             => false,
                'route_name'           => 'ekyna_commerce_customer_group_admin_show',
                'route_parameters_map' => ['groupId' => 'group.id'],
            ])
            ->addColumn('createdAt', 'datetime', [
                'label'    => 'ekyna_core.field.created_at',
                'sortable' => true,
            ])*/
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
            /*->addFilter('id', 'number')
            ->addFilter('firstName', 'text', [
                'label' => 'ekyna_core.field.first_name',
            ])
            ->addFilter('lastName', 'text', [
                'label' => 'ekyna_core.field.last_name',
            ])
            ->addFilter('email', 'text', [
                'label' => 'ekyna_core.field.email',
            ])
            ->addFilter('groups', 'entity', [
                'label'         => 'ekyna_core.field.group',
                'class'         => $this->customerGroupClass,
                'property'      => 'name',
            ])
            ->addFilter('createdAt', 'datetime', [
                'label' => 'ekyna_core.field.created_at',
            ])*/;
    }

    /**
     * {@inheritdoc}
     */
    /*public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('customer', null);

        if (null !== $group = $this->getUserGroup()) {
            $resolver->setDefaults([
                'customize_qb' => function (QueryBuilder $qb, $alias) use ($group) {
                    $qb
                        ->join($alias . '.group', 'g')
                        ->andWhere($qb->expr()->gte('g.position', $group->getPosition()));
                },
            ]);
        }
    }*/

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_commerce_customer_address';
    }
}
