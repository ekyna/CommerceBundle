<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Table\Type\ResourceTableType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Exception\InvalidArgumentException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomerContactType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerContactType extends ResourceTableType
{
    /**
     * @inheritDoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $filters = false;
        /** @var CustomerInterface $customer */
        if (null !== $customer = $options['customer']) {
            $source = $builder->getSource();
            if (!$source instanceof EntitySource) {
                throw new InvalidArgumentException("Expected instance of " . EntitySource::class);
            }

            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, $alias) use ($customer) {
                $qb
                    ->andWhere($qb->expr()->eq($alias . '.customer', ':customer'))
                    ->setParameter('customer', $customer);
            });

            $builder->setFilterable(false);
        } else {
            $filters = true;
            $builder
                ->setExportable(true)
                ->setConfigurable(true)
                ->setProfileable(true);
        }

        $builder
            ->addColumn('email', Ctype\Column\TextType::class, [
                'position' => 10,
            ])
            ->addColumn('identity', Ctype\Column\TextType::class, [
                'property_path' => null,
                'position'      => 20,
            ])
            ->addColumn('title', CType\Column\TextType::class, [
                'label'    => 'ekyna_core.field.title',
                'position' => 40,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_commerce_customer_contact_admin_edit',
                        'route_parameters_map' => [
                            'customerId'        => 'customer.id',
                            'customerContactId' => 'id',
                        ],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_commerce_customer_contact_admin_remove',
                        'route_parameters_map' => [
                            'customerId'        => 'customer.id',
                            'customerContactId' => 'id',
                        ],
                        'permission'           => 'delete',
                    ],
                ],
            ]);

        if (!$filters) {
            return;
        }

        $builder
            ->addFilter('email', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.email',
                'position' => 10,
            ])
            ->addFilter('firstName', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.first_name',
                'position' => 20,
            ])
            ->addFilter('lastName', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.last_name',
                'position' => 30,
            ])
            ->addFilter('title', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.title',
                'position' => 40,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('customer', null)
            ->setAllowedTypes('customer', ['null', CustomerInterface::class]);
    }
}
