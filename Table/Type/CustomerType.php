<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $filters = false;
        if (null !== $parent = $options['parent']) {
            $source = $builder->getSource();
            if ($source instanceof EntitySource) {
                $source->setQueryBuilderInitializer(function (QueryBuilder $qb, $alias) use ($parent) {
                    $qb
                        ->andWhere($qb->expr()->eq($alias . '.parent', ':parent'))
                        ->setParameter('parent', $parent);
                });
            }

            $builder->setFilterable(false);
        } else {
            $filters = true;
            $builder
                ->setExportable(true)
                ->setConfigurable(true)
                ->setProfileable(true)
                ->addColumn('company', CType\Column\TextType::class, [
                    'label'    => 'ekyna_core.field.company',
                    'position' => 30,
                ])
                ->addFilter('company', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_core.field.company',
                    'position' => 30,
                ]);
        }

        $builder
            ->addColumn('number', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.number',
                'route_name'           => 'ekyna_commerce_customer_admin_show',
                'route_parameters_map' => ['customerId' => 'id'],
                'position'             => 10,
            ])
            ->addColumn('name', CType\Column\TextType::class, [
                'label'         => 'ekyna_core.field.name',
                'property_path' => false,
                'position'      => 20,
            ])
            ->addColumn('email', CType\Column\TextType::class, [
                'label'    => 'ekyna_core.field.email',
                'position' => 40,
            ])
            ->addColumn('customerGroup', DType\Column\EntityType::class, [
                'label'                => 'ekyna_commerce.customer_group.label.singular',
                'entity_label'         => 'name',
                'route_name'           => 'ekyna_commerce_customer_group_admin_show',
                'route_parameters_map' => ['customerGroupId' => 'id'],
                'position'             => 50,
            ])
            ->addColumn('creditBalance', CType\Column\NumberType::class, [
                'label'    => 'ekyna_commerce.customer.field.credit_balance',
                'position' => 60,
            ])
            ->addColumn('outstandingBalance', CType\Column\NumberType::class, [
                'label'    => 'ekyna_commerce.customer.field.outstanding_balance',
                'position' => 70,
            ])
            ->addColumn('outstandingLimit', CType\Column\NumberType::class, [
                'label'    => 'ekyna_commerce.customer.field.outstanding_limit',
                'position' => 80,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.created_at',
                'position'    => 90,
                'time_format' => 'none',
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
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
            ]);

        if ($filters) {
            $builder
                ->addFilter('number', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_core.field.number',
                    'position' => 10,
                ])
                ->addFilter('firstName', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_core.field.first_name',
                    'position' => 20,
                ])
                ->addFilter('lastName', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_core.field.last_name',
                    'position' => 25,
                ])
                ->addFilter('email', CType\Filter\TextType::class, [
                    'label'    => 'ekyna_core.field.email',
                    'position' => 40,
                ])
                ->addFilter('customerGroup', DType\Filter\EntityType::class, [
                    'label'        => 'ekyna_core.field.group',
                    'class'        => $this->customerGroupClass,
                    'entity_label' => 'name',
                    'position'     => 50,
                ])
                ->addFilter('creditBalance', CType\Filter\NumberType::class, [
                    'label'    => 'ekyna_commerce.customer.field.credit_balance',
                    'position' => 60,
                ])
                ->addFilter('outstandingBalance', CType\Filter\NumberType::class, [
                    'label'    => 'ekyna_commerce.customer.field.outstanding_balance',
                    'position' => 70,
                ])
                ->addFilter('outstandingLimit', CType\Filter\NumberType::class, [
                    'label'    => 'ekyna_commerce.customer.field.outstanding_limit',
                    'position' => 80,
                ])
                ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                    'label'    => 'ekyna_core.field.created_at',
                    'position' => 90,
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('parent', null)
            ->setAllowedTypes('parent', ['null', CustomerInterface::class]);
    }
}
