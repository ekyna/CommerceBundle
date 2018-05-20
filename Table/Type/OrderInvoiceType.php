<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Type;

use Ekyna\Bundle\CommerceBundle\Model\InvoiceTypes;
use Ekyna\Bundle\CommerceBundle\Table\Action\InvoiceDocumentActionType;
use Ekyna\Bundle\CommerceBundle\Table\Column;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;
use Ekyna\Component\Table\View\RowView;

/**
 * Class OrderInvoiceType
 * @package Ekyna\Bundle\CommerceBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceType extends AbstractOrderListType
{
    /**
     * @inheritDoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        parent::buildTable($builder, $options);

        $builder
            ->addDefaultSort('createdAt', ColumnSort::DESC)
            ->addColumn('number', CType\Column\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addColumn('type', Column\InvoiceTypeType::class, [
                'label'    => 'ekyna_core.field.type',
                'position' => 20,
            ])
            ->addColumn('goodsBase', BType\Column\PriceType::class, [
                'label'         => 'ekyna_commerce.invoice.field.goods_base',
                'currency_path' => 'currency',
                'position'      => 40,
            ])
            ->addColumn('shipmentBase', BType\Column\PriceType::class, [
                'label'         => 'ekyna_commerce.invoice.field.shipment_base',
                'currency_path' => 'currency',
                'position'      => 50,
            ])
            ->addColumn('taxesTotal', BType\Column\PriceType::class, [
                'label'         => 'ekyna_commerce.invoice.field.taxes_total',
                'currency_path' => 'currency',
                'position'      => 60,
            ])
            ->addColumn('grandTotal', BType\Column\PriceType::class, [
                'label'         => 'ekyna_commerce.sale.field.ati_total',
                'currency_path' => 'currency',
                'position'      => 70,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'       => 'ekyna_core.field.created_at',
                'time_format' => 'none',
                'position'    => 80,
            ]);

        if ($options['order'] || $options['customer']) {
            return;
        }

        $builder
            ->addFilter('number', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'position' => 10,
            ])
            ->addFilter('type', CType\Filter\ChoiceType::class, [
                'label'    => 'ekyna_core.field.type',
                'choices'  => InvoiceTypes::getChoices(),
                'position' => 20,
            ])
            // TODO Customer
            ->addFilter('grandTotal', CType\Filter\NumberType::class, [
                'label'    => 'ekyna_commerce.sale.field.ati_total',
                'position' => 70,
            ])
            ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                'label'    => 'ekyna_core.field.created_at',
                'position' => 80,
            ]);

        $builder->addAction('documents', InvoiceDocumentActionType::class, [
            'label' => 'Afficher les factures/avoirs', // TODO trans
        ]);
    }

    /**
     * @inheritDoc
     */
    public function buildRowView(RowView $view, RowInterface $row, array $options)
    {
        /** @var \Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface $invoice */
        $invoice = $row->getData();

        $view->vars['attr']['data-summary'] = json_encode([
            'route'      => 'ekyna_commerce_order_invoice_admin_summary',
            'parameters' => [
                'orderId'        => $invoice->getSale()->getId(),
                'orderInvoiceId' => $invoice->getId(),
            ],
        ]);
    }
}
