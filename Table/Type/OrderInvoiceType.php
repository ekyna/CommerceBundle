<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use DateTime;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice\DeleteAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Invoice\RenderAction;
use Ekyna\Bundle\CommerceBundle\Table\Action\InvoiceDocumentActionType;
use Ekyna\Bundle\CommerceBundle\Table\Column;
use Ekyna\Bundle\CommerceBundle\Table\Filter;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Common\Locking\LockChecker;
use Ekyna\Component\Table\Context\ActiveFilter;
use Ekyna\Component\Table\Context\Context;
use Ekyna\Component\Table\Context\Profile\Profile;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Ekyna\Component\Table\Util\FilterOperator;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use function Symfony\Component\Translation\t;

/**
 * Class OrderInvoiceType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceType extends AbstractOrderListType
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly LockChecker                   $locking
    ) {
    }

    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        parent::buildTable($builder, $options);

        $builder
            ->addDefaultSort('createdAt', ColumnSort::DESC)
            ->addColumn('number', Column\OrderInvoiceType::class, [
                'label'         => t('field.number', [], 'EkynaUi'),
                'property_path' => false,
                'position'      => 10,
            ])
            ->addColumn('credit', CType\Column\BooleanType::class, [
                'label'       => t('field.type', [], 'EkynaUi'),
                'true_label'  => t('credit.label.singular', [], 'EkynaCommerce'),
                'false_label' => t('invoice.label.singular', [], 'EkynaCommerce'),
                'true_class'  => 'label-warning',
                'false_class' => 'label-success',
                'position'    => 20,
            ])
            ->addColumn('title', CType\Column\TextType::class, [
                'label'         => t('field.title', [], 'EkynaUi'),
                'property_path' => 'order.title',
                'position'      => 30,
                'visible'       => false,
            ])
            ->addColumn('goodsBase', BType\Column\PriceType::class, [
                'label'         => t('invoice.field.goods_base', [], 'EkynaCommerce'),
                'currency_path' => 'currency',
                'position'      => 40,
            ])
            ->addColumn('grandTotal', BType\Column\PriceType::class, [
                'label'         => t('sale.field.ati_total', [], 'EkynaCommerce'),
                'currency_path' => 'currency',
                'position'      => 50,
            ])
            ->addColumn('saleTotal', Column\CurrencyType::class, [
                'label'         => t('invoice.field.sale_total', [], 'EkynaCommerce'),
                'property_path' => 'order.grandTotal',
                'subject_path'  => 'order',
                'position'      => 60,
            ])
            ->addColumn('paidTotal', Column\InvoicePaidTotalType::class, [
                'label'         => t('invoice.field.sale_paid', [], 'EkynaCommerce'),
                'property_path' => false,
                'position'      => 70,
            ])
            ->addColumn('dueDate', CType\Column\DateTimeType::class, [
                'label'       => t('sale.field.outstanding_date', [], 'EkynaCommerce'),
                'time_format' => 'none',
                'position'    => 80,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => t('field.created_at', [], 'EkynaUi'),
                'time_format' => 'none',
                'position'    => 90,
            ]);

        $isLocked = function (RowInterface $row): bool {
            return $this->locking->isLocked($row->getData(null));
        };

        $actions = [
            RenderAction::class => [],
            UpdateAction::class => [],
        ];
        if ($this->authorization->isGranted('ROLE_SUPER_ADMIN')) {
            $actions[DeleteAction::class] = [
                'disable' => $isLocked,
            ];
        }

        $builder->addColumn('actions', BType\Column\ActionsType::class, [
            'resource' => $this->dataClass,
            'actions'  => $actions,
        ]);

        if ($options['order'] || $options['customer']) {
            return;
        }

        $builder
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => t('field.number', [], 'EkynaUi'),
                'position' => 10,
            ])
            ->addFilter('credit', CType\Filter\BooleanType::class, [
                'label'    => t('credit.label.singular', [], 'EkynaCommerce'),
                'position' => 20,
            ])
            ->addFilter('grandTotal', CType\Filter\NumberType::class, [
                'label'    => t('sale.field.ati_total', [], 'EkynaCommerce'),
                'position' => 50,
            ])
            ->addFilter('saleTotal', CType\Filter\NumberType::class, [
                'label'         => t('invoice.field.sale_total', [], 'EkynaCommerce'),
                'property_path' => 'order.grandTotal',
                'position'      => 90,
            ])
            ->addFilter('paidTotal', CType\Filter\NumberType::class, [
                'label'    => t('invoice.field.sale_paid', [], 'EkynaCommerce'),
                'position' => 70,
            ])
            ->addFilter('outstandingDate', CType\Filter\DateTimeType::class, [
                'label'         => t('sale.field.outstanding_date', [], 'EkynaCommerce'),
                'property_path' => 'order.outstandingDate',
                'position'      => 80,
            ])
            ->addFilter('initiatorCustomer', Filter\CustomerType::class, [
                'label'         => t('sale.field.initiator_customer', [], 'EkynaCommerce'),
                'property_path' => 'order.initiatorCustomer',
                'position'      => 90,
            ])
            ->addFilter('dueDate', CType\Filter\DateTimeType::class, [
                'label'       => t('sale.field.outstanding_date', [], 'EkynaCommerce'),
                'position' => 100,
            ])
            ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                'label'    => t('field.created_at', [], 'EkynaUi'),
                'position' => 110,
            ]);

        $builder->addAction('documents', InvoiceDocumentActionType::class, [
            'label' => t('invoice.button.documents', [], 'EkynaCommerce'),
        ]);

        $context = new Context();
        $context->setVisibleColumns(
            [
                'number',
                'order',
                'credit',
                'customer',
                'grandTotal',
                'paidTotal',
                'dueDate',
                'createdAt',
                'actions',
            ]
        );

        $filter = (new ActiveFilter('paidTotal_0', 'paidTotal'));
        $filter->setOperator(FilterOperator::EQUAL);
        $filter->setValue(0);
        $context->addActiveFilter($filter);

        $filter = (new ActiveFilter('dueDate_1', 'dueDate'));
        $filter->setOperator(FilterOperator::LOWER_THAN_OR_EQUAL);
        $filter->setValue(new DateTime());
        $context->addActiveFilter($filter);

        $builder->addProfile(Profile::create('unpaid', 'Impayées', $context));
    }
}
