<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\AdminBundle\Table\Type\Filter\ConstantChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\CustomerStates;
use Ekyna\Bundle\CommerceBundle\Table as Type;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Features;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type as DType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class CustomerType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerType extends AbstractResourceType
{
    private Features $features;


    public function __construct(Features $features)
    {
        $this->features = $features;
    }

    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $filters = false;
        if (null !== $parent = $options['parent']) {
            $source = $builder->getSource();
            if ($source instanceof EntitySource) {
                $source->setQueryBuilderInitializer(function (QueryBuilder $qb, string $alias) use ($parent): void {
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
                /*->addColumn('company', CType\Column\TextType::class, [
                    'label'    => t('field.company', [], 'EkynaUi'),
                    'position' => 30,
                ])*/
                ->addFilter('company', CType\Filter\TextType::class, [
                    'label'    => t('field.company', [], 'EkynaUi'),
                    'position' => 30,
                ]);
        }

        $builder
            ->addDefaultSort('createdAt', ColumnSort::DESC)
            ->addColumn('number', BType\Column\AnchorType::class, [
                'label'    => t('field.number', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addColumn('name', CType\Column\TextType::class, [
                'label'         => t('field.name', [], 'EkynaUi'),
                'property_path' => false,
                'sortable'      => false, // TODO Custom column for sorting
                'position'      => 20,
            ])
            ->addColumn('email', CType\Column\TextType::class, [
                'label'    => t('field.email', [], 'EkynaUi'),
                'position' => 40,
            ])
            ->addColumn('customerGroup', DType\Column\EntityType::class, [
                'label'        => t('customer_group.label.singular', [], 'EkynaCommerce'),
                'entity_label' => 'name',
                'position'     => 50,
            ])
            ->addColumn('creditBalance', CType\Column\NumberType::class, [
                'label'    => t('customer.short.credit_balance', [], 'EkynaCommerce'),
                'position' => 60,
            ])
            ->addColumn('outstandingBalance', Type\Column\CustomerOutstandingType::class, [
                'label'    => t('customer.short.outstanding_balance', [], 'EkynaCommerce'),
                'position' => 70,
            ])
            /*->addColumn('outstandingBalance', CType\Column\NumberType::class, [
                'label'    => t('customer.field.outstanding_balance', [], 'EkynaCommerce'),
                'position' => 70,
            ])
            ->addColumn('outstandingLimit', CType\Column\NumberType::class, [
                'label'    => t('sale.field.outstanding_limit', [], 'EkynaCommerce'),
                'position' => 80,
            ])*/
            ->addColumn('inCharge', Type\Column\InChargeType::class, [
                'position' => 100,
            ])
            ->addColumn('state', Type\Column\CustomerStateType::class, [
                'position' => 110,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => t('field.created_at', [], 'EkynaUi'),
                'position'    => 120,
                'time_format' => 'none',
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    UpdateAction::class,
                    DeleteAction::class,
                ],
            ]);

        if ($this->features->isEnabled(Features::LOYALTY)) {
            $builder->addColumn('loyaltyPoints', CType\Column\NumberType::class, [
                'label'    => t('customer.field.loyalty_points', [], 'EkynaCommerce'),
                'position' => 90,
            ]);
        }

        if ($filters) {
            $builder
                ->addFilter('number', CType\Filter\TextType::class, [
                    'label'    => t('field.number', [], 'EkynaUi'),
                    'position' => 10,
                ])
                ->addFilter('firstName', CType\Filter\TextType::class, [
                    'label'    => t('field.first_name', [], 'EkynaUi'),
                    'position' => 20,
                ])
                ->addFilter('lastName', CType\Filter\TextType::class, [
                    'label'    => t('field.last_name', [], 'EkynaUi'),
                    'position' => 23,
                ])
                ->addFilter('companyNumber', CType\Filter\TextType::class, [
                    'label'    => t('customer.field.company_number', [], 'EkynaCommerce'),
                    'position' => 26,
                ])
                ->addFilter('email', CType\Filter\TextType::class, [
                    'label'    => t('field.email', [], 'EkynaUi'),
                    'position' => 40,
                ])
                ->addFilter('postalCode', CType\Filter\TextType::class, [
                    'label'         => t('field.postal_code', [], 'EkynaUi'),
                    'property_path' => 'addresses.postalCode',
                    'position'      => 50,
                ])
                ->addFilter('city', CType\Filter\TextType::class, [
                    'label'         => t('field.city', [], 'EkynaUi'),
                    'property_path' => 'addresses.city',
                    'position'      => 53,
                ])
                ->addFilter('country', ResourceType::class, [
                    'resource'      => 'ekyna_commerce.country',
                    'property_path' => 'addresses.country',
                    'position'      => 54,
                ])
                ->addFilter('phone', Type\Filter\PhoneNumberType::class, [
                    'label'        => t('field.phone', [], 'EkynaUi'),
                    'trans_domain' => 'EkynaUi',
                    'position'     => 56,
                ])
                ->addFilter('parent', CType\Filter\BooleanType::class, [
                    'label'    => t('customer.field.parent', [], 'EkynaCommerce'),
                    'mode'     => CType\Filter\BooleanType::MODE_IS_NULL,
                    'position' => 60,
                ])
                ->addFilter('customerGroup', ResourceType::class, [
                    'resource' => 'ekyna_commerce.customer_group',
                    'position' => 70,
                ])
                ->addFilter('creditBalance', CType\Filter\NumberType::class, [
                    'label'    => t('customer.field.credit_balance', [], 'EkynaCommerce'),
                    'position' => 80,
                ])
                ->addFilter('outstandingBalance', CType\Filter\NumberType::class, [
                    'label'    => t('customer.field.outstanding_balance', [], 'EkynaCommerce'),
                    'position' => 90,
                ])
                ->addFilter('outstandingLimit', CType\Filter\NumberType::class, [
                    'label'    => t('sale.field.outstanding_limit', [], 'EkynaCommerce'),
                    'position' => 100,
                ])
                ->addFilter('inCharge', Type\Filter\InChargeType::class, [
                    'position' => 110,
                ])
                ->addFilter('defaultPaymentMethod', ResourceType::class, [
                    'label'    => t('customer.field.default_payment_method', [], 'EkynaCommerce'),
                    'resource' => 'ekyna_commerce.payment_method',
                    'position' => 115,
                ])
                ->addFilter('state', ConstantChoiceType::class, [
                    'label'    => t('field.status', [], 'EkynaUi'),
                    'class'    => CustomerStates::class,
                    'position' => 120,
                ])
                ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                    'label'    => t('field.created_at', [], 'EkynaUi'),
                    'position' => 130,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('parent', null)
            ->setAllowedTypes('parent', ['null', CustomerInterface::class]);
    }
}
