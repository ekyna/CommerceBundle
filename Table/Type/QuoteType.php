<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\AdminBundle\Table\Type\Filter\ConstantChoiceType;
use Ekyna\Bundle\CmsBundle\Table\Column\TagsType;
use Ekyna\Bundle\CommerceBundle\Model;
use Ekyna\Bundle\CommerceBundle\Table as Type;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Exception\UnexpectedTypeException;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_merge;
use function Symfony\Component\Translation\t;

/**
 * Class QuoteType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $filters = false;
        /** @var Model\CustomerInterface $customer */
        if (null !== $customer = $options['customer']) {
            $source = $builder->getSource();
            if (!$source instanceof EntitySource) {
                throw new UnexpectedTypeException($source, EntitySource::class);
            }

            $source->setQueryBuilderInitializer(function (QueryBuilder $qb, string $alias) use ($customer): void {
                if ($customer->hasChildren()) {
                    $qb
                        ->andWhere($qb->expr()->in($alias . '.customer', ':customers'))
                        ->setParameter('customers', array_merge([$customer], $customer->getChildren()->toArray()));
                } else {
                    $qb
                        ->andWhere($qb->expr()->eq($alias . '.customer', ':customer'))
                        ->setParameter('customer', $customer);
                }
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
            ->addDefaultSort('createdAt', ColumnSort::DESC)
            ->addColumn('flags', Type\Column\SaleFlagsType::class, [
                'property_path' => false,
                'position'      => 5,
            ])
            ->addColumn('number', Type\Column\QuoteType::class, [
                'label'         => t('field.number', [], 'EkynaUi'),
                'property_path' => false,
                'position'      => 10,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => t('field.date', [], 'EkynaUi'),
                'position'    => 20,
                'time_format' => 'none',
            ])
            ->addColumn('title', CType\Column\TextType::class, [
                'label'    => t('field.title', [], 'EkynaUi'),
                'position' => 40,
            ])
            ->addColumn('voucherNumber', CType\Column\TextType::class, [
                'label'    => t('sale.field.voucher_number', [], 'EkynaCommerce'),
                'position' => 45,
            ])
            ->addColumn('grandTotal', Type\Column\CurrencyType::class, [
                'label'    => t('sale.field.ati_total', [], 'EkynaCommerce'),
                'position' => 50,
            ])
            ->addColumn('paidTotal', Type\Column\CurrencyType::class, [
                'label'    => t('sale.field.paid_total', [], 'EkynaCommerce'),
                'position' => 60,
            ])
            ->addColumn('state', Type\Column\SaleStateType::class, [
                'position' => 70,
            ])
            ->addColumn('paymentState', Type\Column\PaymentStateType::class, [
                'position' => 80,
            ])
            /*->addColumn('inCharge', Type\Column\InChargeType::class, [
                'position' => 90,
            ])*/
            ->addColumn('tags', TagsType::class, [
                'property_path' => 'allTags',
                'position'      => 100,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    UpdateAction::class,
                    DeleteAction::class,
                ],
            ]);

        if (null === $customer || $customer->hasChildren()) {
            $builder->addColumn('customer', Type\Column\SaleCustomerType::class, [
                'position' => 30,
            ]);
        }

        if (!$filters) {
            return;
        }
        $builder
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => t('field.number', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                'label'    => t('field.created_at', [], 'EkynaUi'),
                'position' => 20,
                'time'     => false,
            ])
            ->addFilter('email', CType\Filter\TextType::class, [
                'label'    => t('field.email', [], 'EkynaUi'),
                'position' => 30,
            ])
            ->addFilter('company', CType\Filter\TextType::class, [
                'label'    => t('field.company', [], 'EkynaUi'),
                'position' => 31,
            ])
            ->addFilter('firstName', CType\Filter\TextType::class, [
                'label'    => t('field.first_name', [], 'EkynaUi'),
                'position' => 32,
            ])
            ->addFilter('lastName', CType\Filter\TextType::class, [
                'label'    => t('field.last_name', [], 'EkynaUi'),
                'position' => 33,
            ])
            ->addFilter('companyNumber', CType\Filter\TextType::class, [
                'label'         => t('customer.field.company_number', [], 'EkynaCommerce'),
                'property_path' => 'customer.companyNumber',
                'position'      => 34,
            ])
            ->addFilter('customerGroup', ResourceType::class, [
                'resource' => 'ekyna_commerce.customer_group',
                'position' => 35,
            ])
            ->addFilter('title', CType\Filter\TextType::class, [
                'label'    => t('field.title', [], 'EkynaUi'),
                'position' => 40,
            ])
            ->addFilter('voucherNumber', CType\Filter\TextType::class, [
                'label'    => t('sale.field.voucher_number', [], 'EkynaCommerce'),
                'position' => 45,
            ])
            ->addFilter('grandTotal', CType\Filter\NumberType::class, [
                'label'    => t('sale.field.ati_total', [], 'EkynaCommerce'),
                'position' => 50,
            ])
            ->addFilter('paidTotal', CType\Filter\NumberType::class, [
                'label'    => t('sale.field.paid_total', [], 'EkynaCommerce'),
                'position' => 60,
            ])
            ->addFilter('state', ConstantChoiceType::class, [
                'label'    => t('field.status', [], 'EkynaCommerce'),
                'class'    => Model\QuoteStates::class,
                'position' => 70,
            ])
            ->addFilter('paymentState', Type\Filter\SalePaymentStateType::class, [
                'position' => 80,
            ])
            ->addFilter('inCharge', Type\Filter\InChargeType::class, [
                'position' => 90,
            ])
            ->addFilter('tags', Type\Filter\SaleTagsType::class, [
                'position' => 100,
            ])
            ->addFilter('inCharge', Type\Filter\InChargeType::class, [
                'position' => 110,
            ])
            ->addFilter('subject', Type\Filter\SaleSubjectType::class, [
                'position' => 150,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('customer', null)
            ->setAllowedTypes('customer', ['null', Model\CustomerInterface::class]);
    }
}
