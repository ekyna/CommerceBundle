<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Common\SaleHelper;
use Ekyna\Bundle\CommerceBundle\Service\Common\SaleRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Common\SaleViewHelper;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Document\Util\DocumentUtil;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * Class SaleExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleExtension extends AbstractExtension
{
    public function getTests(): array
    {
        return [
            new TwigTest(
                'sale',
                [SaleHelper::class, 'isSale']
            ),
            new TwigTest(
                'sale_cart',
                [SaleHelper::class, 'isCart']
            ),
            new TwigTest(
                'sale_quote',
                [SaleHelper::class, 'isQuote']
            ),
            new TwigTest(
                'sale_order',
                [SaleHelper::class, 'isOrder']
            ),
            new TwigTest(
                'sale_item',
                [SaleHelper::class, 'isSaleItem']
            ),
            new TwigTest(
                'sale_stockable_state',
                [SaleHelper::class, 'isSaleStockable']
            ),
            new TwigTest(
                'sale_preparable',
                [SaleHelper::class, 'isSalePreparable']
            ),
            new TwigTest(
                'sale_preparing',
                [SaleHelper::class, 'isSalePreparing']
            ),
            new TwigTest(
                'sale_fully_shipped',
                [SaleHelper::class, 'isSaleFullyShipped']
            ),
            new TwigTest(
                'sale_fully_invoiced',
                [SaleHelper::class, 'isSaleFullyInvoiced']
            ),
            new TwigTest(
                'sale_with_payment',
                [SaleHelper::class, 'isSaleWithPayment']
            ),
            new TwigTest(
                'sale_with_refund',
                [SaleHelper::class, 'isSaleWithRefund']
            ),
            new TwigTest(
                'sale_with_shipment',
                [SaleHelper::class, 'isSaleWithShipment']
            ),
            new TwigTest(
                'sale_with_return',
                [SaleHelper::class, 'isSaleWithReturn']
            ),
            new TwigTest(
                'sale_with_invoice',
                [SaleHelper::class, 'isSaleWithInvoice']
            ),
            new TwigTest(
                'sale_with_credit',
                [SaleHelper::class, 'isSaleWithCredit']
            ),
            new TwigTest(
                'sale_with_attachment',
                [SaleHelper::class, 'isSaleWithAttachment']
            ),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'sale_state_label',
                [ConstantsHelper::class, 'renderSaleStateLabel']
            ),
            new TwigFilter(
                'sale_state_badge',
                [ConstantsHelper::class, 'renderSaleStateBadge'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'sale_flashes',
                [SaleHelper::class, 'getSaleFlashes']
            ),
            new TwigFilter(
                'sale_payments',
                [SaleHelper::class, 'getSalePayments']
            ),
            new TwigFilter(
                'sale_shipments',
                [SaleHelper::class, 'getSaleShipments']
            ),
            new TwigFilter(
                'sale_returns',
                [SaleHelper::class, 'getSaleReturns']
            ),
            new TwigFilter(
                'sale_attachments',
                [SaleHelper::class, 'getSaleAttachments']
            ),
            new TwigFilter(
                'sale_shipment_amount',
                [SaleRenderer::class, 'getSaleShipmentAmount']
            ),
            new TwigFilter(
                'sale_view',
                [SaleViewHelper::class, 'buildSaleView']
            ),
            new TwigFilter(
                'sale_editable_document_types',
                [DocumentUtil::class, 'getSaleEditableDocumentTypes']
            ),
            new TwigFilter(
                'sale_support_document_type',
                [DocumentUtil::class, 'isSaleSupportsDocumentType']
            ),
        ];
    }

    public function getFunctions(): array
    {
        return [
            // Renders the sale view
            new TwigFunction(
                'render_sale_view',
                [SaleRenderer::class, 'renderSaleView'],
                ['is_safe' => ['html']]
            ),
            // Renders the sale transform button
            new TwigFunction(
                'sale_transform_btn',
                [SaleRenderer::class, 'renderSaleTransformButton'],
                ['is_safe' => ['html']]
            ),
            // Renders the sale duplicate button
            new TwigFunction(
                'sale_duplicate_btn',
                [SaleRenderer::class, 'renderSaleDuplicateButton'],
                ['is_safe' => ['html']]
            ),
            // Renders the sale export button
            new TwigFunction(
                'sale_export_btn',
                [SaleRenderer::class, 'renderSaleExportButton'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
