<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Common\SaleRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Common\SaleViewHelper;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Cart\Model as Cart;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartStates;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Document\Util\DocumentUtil;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Order\Model as Order;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Quote\Model as Quote;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteStates;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

use function Symfony\Component\Translation\t;

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
            new TwigTest('sale', [$this, 'isSale']),
            new TwigTest('sale_cart', [$this, 'isCart']),
            new TwigTest('sale_quote', [$this, 'isQuote']),
            new TwigTest('sale_order', [$this, 'isOrder']),
            new TwigTest('sale_item', [$this, 'isSaleItem']),
            new TwigTest('sale_stockable_state', [$this, 'isSaleStockable']),
            new TwigTest('sale_preparable', [$this, 'isSalePreparable']),
            new TwigTest('sale_preparing', [$this, 'isSalePreparing']),
            new TwigTest('sale_with_payment', [$this, 'isSaleWithPayment']),
            new TwigTest('sale_with_refund', [$this, 'isSaleWithRefund']),
            new TwigTest('sale_with_shipment', [$this, 'isSaleWithShipment']),
            new TwigTest('sale_with_return', [$this, 'isSaleWithReturn']),
            new TwigTest('sale_with_invoice', [$this, 'isSaleWithInvoice']),
            new TwigTest('sale_with_credit', [$this, 'isSaleWithCredit']),
            new TwigTest('sale_with_attachment', [$this, 'isSaleWithAttachment']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('sale_state_label', [ConstantsHelper::class, 'renderSaleStateLabel']),
            new TwigFilter('sale_state_badge', [ConstantsHelper::class, 'renderSaleStateBadge'], ['is_safe' => ['html']]),
            new TwigFilter('sale_flashes', [$this, 'getSaleFlashes']),
            new TwigFilter('sale_payments', [$this, 'getSalePayments']),
            new TwigFilter('sale_shipments', [$this, 'getSaleShipments']),
            new TwigFilter('sale_returns', [$this, 'getSaleReturns']),
            new TwigFilter('sale_attachments', [$this, 'getSaleAttachments']),
            new TwigFilter('sale_shipment_amount', [SaleRenderer::class, 'getSaleShipmentAmount']),
            new TwigFilter('sale_view', [SaleViewHelper::class, 'buildSaleView']),
            new TwigFilter('sale_editable_document_types', [DocumentUtil::class, 'getSaleEditableDocumentTypes']),
            new TwigFilter('sale_support_document_type', [DocumentUtil::class, 'isSaleSupportsDocumentType']),
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

    public function isSale(object $subject): bool
    {
        return $subject instanceof Common\SaleInterface;
    }

    public function isCart(object $subject): bool
    {
        return $subject instanceof Cart\CartInterface;
    }

    public function isQuote(object $subject): bool
    {
        return $subject instanceof Quote\QuoteInterface;
    }

    public function isOrder(object $subject): bool
    {
        return $subject instanceof Order\OrderInterface;
    }

    public function isSaleItem(object $subject): bool
    {
        return $subject instanceof Common\SaleItemInterface;
    }

    /**
     * Returns whether the sale is in a stockable state.
     */
    public function isSaleStockable(Common\SaleInterface $sale): bool
    {
        if ($sale instanceof Order\OrderInterface) {
            return Order\OrderStates::isStockableState($sale->getState());
        }

        return false;
    }

    /**
     * Returns whether the sale is in a preparable state.
     */
    public function isSalePreparable(Common\SaleInterface $sale): bool
    {
        if ($sale instanceof Order\OrderInterface) {
            if ($sale->isReleased()) {
                return false;
            }

            if ($sale->getItems()->isEmpty()) {
                return false;
            }

            return Shipment\ShipmentStates::isPreparableState($sale->getShipmentState());
        }

        return false;
    }

    /**
     * Returns whether the sale is in a preparation state.
     */
    public function isSalePreparing(Common\SaleInterface $sale): bool
    {
        if ($sale instanceof Order\OrderInterface) {
            return $sale->getShipmentState() === Shipment\ShipmentStates::STATE_PREPARATION;
        }

        return false;
    }

    /**
     * Returns whether the sale has at least one payment (which is not 'new').
     */
    public function isSaleWithPayment(Common\SaleInterface $sale): bool
    {
        foreach ($sale->getPayments(true) as $payment) {
            if ($payment->getState() !== Payment\PaymentStates::STATE_NEW) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether the sale has at least one refund (which is not 'new').
     */
    public function isSaleWithRefund(Common\SaleInterface $sale): bool
    {
        foreach ($sale->getPayments(false) as $payment) {
            if ($payment->getState() !== Payment\PaymentStates::STATE_NEW) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether the sale has at least one shipment (which is not 'new' or a return).
     */
    public function isSaleWithShipment(Common\SaleInterface $sale): bool
    {
        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return false;
        }

        foreach ($sale->getShipments(true) as $shipment) {
            if ($shipment->getState() !== Shipment\ShipmentStates::STATE_NEW) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether the sale has at least one return shipment (which is not 'new').
     */
    public function isSaleWithReturn(Common\SaleInterface $sale): bool
    {
        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return false;
        }

        foreach ($sale->getShipments(false) as $shipment) {
            if ($shipment->getState() !== Shipment\ShipmentStates::STATE_NEW) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether the sale has at least one invoice (which is not a credit).
     */
    public function isSaleWithInvoice(Common\SaleInterface $sale): bool
    {
        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            return false;
        }

        return !$sale->getInvoices(true)->isEmpty();
    }

    /**
     * Returns whether the sale has at least one credit invoice.
     */
    public function isSaleWithCredit(Common\SaleInterface $sale): bool
    {
        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            return false;
        }

        return !$sale->getInvoices(false)->isEmpty();
    }

    /**
     * Returns whether the sale has at least one attachment (which is not internal).
     */
    public function isSaleWithAttachment(Common\SaleInterface $sale): bool
    {
        foreach ($sale->getAttachments() as $attachment) {
            if (!$attachment->isInternal()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the sale flashes.
     */
    public function getSaleFlashes(Common\SaleInterface $sale): array
    {
        if ($sale instanceof CartInterface) {
            if ($sale->getState() === CartStates::STATE_ACCEPTED) {
                return [
                    'info' => [
                        t('cart.message.transformation_to_order_is_ready', [], 'EkynaCommerce'),
                    ]
                ];
            }

            return [];
        }

        if ($sale instanceof QuoteInterface) {
            if ($sale->getState() === QuoteStates::STATE_ACCEPTED) {
                return [
                    'info' => [
                        t('quote.message.transformation_to_order_is_ready', [], 'EkynaCommerce'),
                    ]
                ];
            }

            return [];
        }

        if ($sale->canBeReleased()) {
            return [
                'warning' => [
                    t('order.message.can_be_released', [], 'EkynaCommerce'),
                ]
            ];
        }

        return [];
    }

    /**
     * Returns the sale's payments (which are not 'new').
     *
     * @return array<Payment\PaymentInterface>
     */
    public function getSalePayments(Common\SaleInterface $sale): array
    {
        return $sale->getPayments()->filter(function (Payment\PaymentInterface $payment) {
            return $payment->getState() !== Payment\PaymentStates::STATE_NEW;
        })->toArray();
    }

    /**
     * Returns the sale's shipments (which are not 'new' or returns).
     *
     * @return array<Shipment\ShipmentInterface>
     */
    public function getSaleShipments(Common\SaleInterface $sale): array
    {
        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return [];
        }

        return $sale->getShipments()->filter(function (Shipment\ShipmentInterface $shipment) {
            return !$shipment->isReturn() && $shipment->getState() !== Shipment\ShipmentStates::STATE_NEW;
        })->toArray();
    }

    /**
     * Returns the sale's returns (which are not 'new').
     *
     * @return array<Shipment\ShipmentInterface>
     */
    public function getSaleReturns(Common\SaleInterface $sale): array
    {
        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return [];
        }

        return $sale->getShipments()->filter(function (Shipment\ShipmentInterface $shipment) {
            return $shipment->isReturn() && $shipment->getState() !== Shipment\ShipmentStates::STATE_NEW;
        })->toArray();
    }

    /**
     * Returns the sale's attachments (which are not internal).
     *
     * @return array<Common\SaleAttachmentInterface>
     */
    public function getSaleAttachments(Common\SaleInterface $sale): array
    {
        return $sale->getAttachments()->filter(function (Common\AttachmentInterface $attachment) {
            return !$attachment->isInternal();
        })->toArray();
    }
}
