<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Component\Commerce\Cart\Model as Cart;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartStates;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Order\Model as Order;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Quote\Model as Quote;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteStates;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;

use function Symfony\Component\Translation\t;

/**
 * Class SaleHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleHelper
{
    public static function isSale(object $subject): bool
    {
        return $subject instanceof Common\SaleInterface;
    }

    public static function isCart(object $subject): bool
    {
        return $subject instanceof Cart\CartInterface;
    }

    public static function isQuote(object $subject): bool
    {
        return $subject instanceof Quote\QuoteInterface;
    }

    public static function isOrder(object $subject): bool
    {
        return $subject instanceof Order\OrderInterface;
    }

    public static function isSaleItem(object $subject): bool
    {
        return $subject instanceof Common\SaleItemInterface;
    }

    /**
     * Returns whether the sale is in a stockable state.
     */
    public static function isSaleStockable(Common\SaleInterface $sale): bool
    {
        if ($sale instanceof Order\OrderInterface) {
            return Order\OrderStates::isStockableState($sale->getState());
        }

        return false;
    }

    /**
     * Returns whether the sale is in a preparable state.
     */
    public static function isSalePreparable(Common\SaleInterface $sale): bool
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
    public static function isSalePreparing(Common\SaleInterface $sale): bool
    {
        if ($sale instanceof Order\OrderInterface) {
            return $sale->getShipmentState() === Shipment\ShipmentStates::STATE_PREPARATION;
        }

        return false;
    }

    /**
     * Returns whether the sale is fully shipped.
     */
    public static function isSaleFullyShipped(Common\SaleInterface $sale): bool
    {
        if ($sale instanceof Order\OrderInterface) {
            return in_array($sale->getShipmentState(), [
                Shipment\ShipmentStates::STATE_COMPLETED,
                Shipment\ShipmentStates::STATE_RETURNED,
            ], true);
        }

        return false;
    }

    /**
     * Returns whether the sale is fully invoiced.
     */
    public static function isSaleFullyInvoiced(Common\SaleInterface $sale): bool
    {
        if ($sale instanceof Order\OrderInterface) {
            return in_array($sale->getShipmentState(), [
                Invoice\InvoiceStates::STATE_COMPLETED,
                Invoice\InvoiceStates::STATE_CREDITED,
            ], true);
        }

        return false;
    }

    /**
     * Returns whether the sale has at least one payment (which is not 'new').
     */
    public static function isSaleWithPayment(Common\SaleInterface $sale): bool
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
    public static function isSaleWithRefund(Common\SaleInterface $sale): bool
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
    public static function isSaleWithShipment(Common\SaleInterface $sale): bool
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
    public static function isSaleWithReturn(Common\SaleInterface $sale): bool
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
    public static function isSaleWithInvoice(Common\SaleInterface $sale): bool
    {
        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            return false;
        }

        return !$sale->getInvoices(true)->isEmpty();
    }

    /**
     * Returns whether the sale has at least one credit invoice.
     */
    public static function isSaleWithCredit(Common\SaleInterface $sale): bool
    {
        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            return false;
        }

        return !$sale->getInvoices(false)->isEmpty();
    }

    /**
     * Returns whether the sale has at least one attachment (which is not internal).
     */
    public static function isSaleWithAttachment(Common\SaleInterface $sale): bool
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
    public static function getSaleFlashes(Common\SaleInterface $sale): array
    {
        if ($sale instanceof CartInterface) {
            if ($sale->getState() === CartStates::STATE_ACCEPTED) {
                return [
                    'info' => [
                        t('cart.message.transformation_to_order_is_ready', [], 'EkynaCommerce'),
                    ],
                ];
            }

            return [];
        }

        if ($sale instanceof QuoteInterface) {
            if ($sale->getState() === QuoteStates::STATE_ACCEPTED) {
                return [
                    'info' => [
                        t('quote.message.transformation_to_order_is_ready', [], 'EkynaCommerce'),
                    ],
                ];
            }

            return [];
        }

        if ($sale->canBeReleased()) {
            return [
                'warning' => [
                    t('order.message.can_be_released', [], 'EkynaCommerce'),
                ],
            ];
        }

        return [];
    }

    /**
     * Returns the sale's payments (which are not 'new').
     *
     * @return array<Payment\PaymentInterface>
     */
    public static function getSalePayments(Common\SaleInterface $sale): array
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
    public static function getSaleShipments(Common\SaleInterface $sale): array
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
    public static function getSaleReturns(Common\SaleInterface $sale): array
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
    public static function getSaleAttachments(Common\SaleInterface $sale): array
    {
        return $sale->getAttachments()->filter(function (Common\AttachmentInterface $attachment) {
            return !$attachment->isInternal();
        })->toArray();
    }
}
