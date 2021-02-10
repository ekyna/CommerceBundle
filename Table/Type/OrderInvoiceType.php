<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\CommerceBundle\Table\Action\InvoiceDocumentActionType;
use Ekyna\Bundle\CommerceBundle\Table\Column;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Commerce\Common\Locking\LockChecker;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\Source\Row;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class OrderInvoiceType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceType extends AbstractOrderListType
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;

    /**
     * @var LockChecker
     */
    private $locking;


    /**
     * Constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param LockChecker                   $locking
     * @param string                        $class
     */
    public function __construct(AuthorizationCheckerInterface $authorization, LockChecker $locking, string $class)
    {
        parent::__construct($class);

        $this->authorization = $authorization;
        $this->locking = $locking;
    }

    /**
     * @inheritDoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        parent::buildTable($builder, $options);

        $builder
            ->addDefaultSort('createdAt', ColumnSort::DESC)
            ->addColumn('number', Column\OrderInvoiceType::class, [
                'label'         => 'ekyna_core.field.number',
                'property_path' => false,
                'position'      => 10,
            ])
            ->addColumn('credit', CType\Column\BooleanType::class, [
                'label'       => 'ekyna_core.field.type',
                'true_label'  => 'ekyna_commerce.credit.label.singular',
                'false_label' => 'ekyna_commerce.invoice.label.singular',
                'true_class'  => 'label-warning',
                'false_class' => 'label-success',
                'position'    => 20,
            ])
            ->addColumn('grandTotal', BType\Column\PriceType::class, [
                'label'         => 'ekyna_commerce.sale.field.ati_total',
                'currency_path' => 'currency',
                'position'      => 50,
            ])
            ->addColumn('saleTotal', Column\CurrencyType::class, [
                'label'         => 'ekyna_commerce.invoice.field.sale_total',
                'property_path' => 'order.grandTotal',
                'subject_path'  => 'order',
                'position'      => 60,
            ])
            ->addColumn('paidTotal', Column\InvoicePaidTotalType::class, [
                'label'         => 'ekyna_commerce.invoice.field.sale_paid',
                'property_path' => false,
                'position'      => 70,
            ])
            ->addColumn('dueDate', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_commerce.sale.field.outstanding_date',
                'time_format' => 'none',
                'position'    => 80,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.created_at',
                'time_format' => 'none',
                'position'    => 90,
            ]);

        $isLocked = function (Row $row) {
            return $this->locking->isLocked($row->getData());
        };

        $buttons = [
            [
                'label'                => 'ekyna_core.button.download',
                'icon'                 => 'download',
                'class'                => 'primary',
                'route_name'           => 'ekyna_commerce_order_invoice_admin_render',
                'route_parameters_map' => [
                    'orderId'        => 'order.id',
                    'orderInvoiceId' => 'id',
                ],
                'target'               => '_blank',
                //'permission' => 'EDIT', // TODO see admin actions type extension
            ],
            [
                'label'                => 'ekyna_core.button.edit',
                'icon'                 => 'pencil',
                'class'                => 'warning',
                'route_name'           => 'ekyna_commerce_order_invoice_admin_edit',
                'route_parameters_map' => [
                    'orderId'        => 'order.id',
                    'orderInvoiceId' => 'id',
                ],
                'disable'              => $isLocked,
                //'permission' => 'EDIT', // TODO see admin actions type extension
            ],
        ];

        if ($this->authorization->isGranted('ROLE_SUPER_ADMIN')) {
            $buttons[] = [
                'label'                => 'ekyna_core.button.remove',
                'icon'                 => 'trash',
                'class'                => 'danger',
                'route_name'           => 'ekyna_commerce_order_invoice_admin_remove',
                'route_parameters_map' => [
                    'orderId'        => 'order.id',
                    'orderInvoiceId' => 'id',
                ],
                'disable'              => $isLocked,
                //'permission' => 'EDIT', // TODO see admin actions type extension
            ];
        }

        $builder->addColumn('actions', BType\Column\ActionsType::class, [
            'buttons' => $buttons,
        ]);

        if ($options['order'] || $options['customer']) {
            return;
        }

        $builder
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addFilter('credit', CType\Filter\BooleanType::class, [
                'label'    => 'ekyna_commerce.credit.label.singular',
                'position' => 20,
            ])
            ->addFilter('grandTotal', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_commerce.sale.field.ati_total',
                'position' => 50,
            ])
            ->addFilter('saleTotal', CType\Filter\NumberType::class, [
                'label'         => 'ekyna_commerce.invoice.field.sale_total',
                'property_path' => 'order.grandTotal',
                'position'      => 50,
            ])
            ->addFilter('outstandingDate', CType\Filter\DateTimeType::class, [
                'label'         => 'ekyna_commerce.sale.field.outstanding_date',
                'property_path' => 'order.outstandingDate',
                'position'      => 60,
            ])
            ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                'label'    => 'ekyna_core.field.created_at',
                'position' => 70,
            ]);

        $builder->addAction('documents', InvoiceDocumentActionType::class, [
            'label' => 'Afficher les factures/avoirs', // TODO trans
        ]);
    }
}
