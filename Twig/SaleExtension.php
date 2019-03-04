<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\CoreBundle\Service\Ui\UiRenderer;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\View\ViewBuilder;
use Ekyna\Component\Commerce\Common\View\SaleView;
use Ekyna\Component\Commerce\Document\Util\SaleDocumentUtil;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Order\Model as Order;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class SaleExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleExtension extends \Twig_Extension
{
    /**
     * @var ConstantsHelper
     */
    private $constantHelper;

    /**
     * @var ContextProviderInterface
     */
    private $contextProvider;

    /**
     * @var ViewBuilder
     */
    private $viewBuilder;

    /**
     * @var UiRenderer
     */
    private $uiRenderer;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;


    /**
     * Constructor.
     *
     * @param ConstantsHelper          $constantHelper
     * @param ContextProviderInterface $contextProvider
     * @param ViewBuilder              $viewBuilder
     * @param UiRenderer               $uiRenderer
     * @param UrlGeneratorInterface    $urlGenerator
     */
    public function __construct(
        ConstantsHelper $constantHelper,
        ContextProviderInterface $contextProvider,
        ViewBuilder $viewBuilder,
        UiRenderer $uiRenderer,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->constantHelper = $constantHelper;
        $this->contextProvider = $contextProvider;
        $this->viewBuilder = $viewBuilder;
        $this->uiRenderer = $uiRenderer;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritDoc
     */
    public function getTests()
    {
        return [
            new \Twig_SimpleTest(
                'sale_stockable_state',
                [$this, 'isSaleStockableSale']
            ),
            new \Twig_SimpleTest(
                'sale_preparable',
                [$this, 'isSalePreparable']
            ),
            new \Twig_SimpleTest(
                'sale_preparing',
                [$this, 'isSalePreparing']
            ),
            new \Twig_SimpleTest(
                'sale_with_payment',
                [$this, 'isSaleWithPayment']
            ),
            new \Twig_SimpleTest(
                'sale_with_shipment',
                [$this, 'isSaleWithShipment']
            ),
            new \Twig_SimpleTest(
                'sale_with_return',
                [$this, 'isSaleWithReturn']
            ),
            new \Twig_SimpleTest(
                'sale_with_invoice',
                [$this, 'isSaleWithInvoice']
            ),
            new \Twig_SimpleTest(
                'sale_with_credit',
                [$this, 'isSaleWithCredit']
            ),
            new \Twig_SimpleTest(
                'sale_with_attachment',
                [$this, 'isSaleWithAttachment']
            ),
            new \Twig_SimpleTest(
                'sale_cart',
                [$this, 'isSaleCart']
            ),
            new \Twig_SimpleTest(
                'sale_quote',
                [$this, 'isSaleQuote']
            ),
            new \Twig_SimpleTest(
                'sale_order',
                [$this, 'isSaleOrder']
            ),
        ];
    }

    /**
     * Returns whether the given sale is a cart.
     *
     * @param Common\SaleInterface $sale
     *
     * @return bool
     */
    public function isSaleCart(Common\SaleInterface $sale)
    {
        return $sale instanceof CartInterface;
    }

    /**
     * Returns whether the given sale is a quote.
     *
     * @param Common\SaleInterface $sale
     *
     * @return bool
     */
    public function isSaleQuote(Common\SaleInterface $sale)
    {
        return $sale instanceof QuoteInterface;
    }

    /**
     * Returns whether the given sale is an order.
     *
     * @param Common\SaleInterface $sale
     *
     * @return bool
     */
    public function isSaleOrder(Common\SaleInterface $sale)
    {
        return $sale instanceof OrderInterface;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'sale_state_label',
                [$this->constantHelper, 'renderSaleStateLabel']
            ),
            new \Twig_SimpleFilter(
                'sale_state_badge',
                [$this->constantHelper, 'renderSaleStateBadge'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'sale_payments',
                [$this, 'getSalePayments']
            ),
            new \Twig_SimpleFilter(
                'sale_shipments',
                [$this, 'getSaleShipments']
            ),
            new \Twig_SimpleFilter(
                'sale_returns',
                [$this, 'getSaleReturns']
            ),
            new \Twig_SimpleFilter(
                'sale_invoices',
                [$this, 'getSaleInvoices']
            ),
            new \Twig_SimpleFilter(
                'sale_credits',
                [$this, 'getSaleCredits']
            ),
            new \Twig_SimpleFilter(
                'sale_attachments',
                [$this, 'getSaleAttachments']
            ),
            new \Twig_SimpleFilter(
                'sale_shipment_amount',
                [$this, 'getSaleShipmentAmount']
            ),
            // Builds the sale view form the sale
            new \Twig_SimpleFilter(
                'sale_view',
                [$this->viewBuilder, 'buildSaleView']
            ),
            new \Twig_SimpleFilter(
                'sale_editable_document_types',
                [SaleDocumentUtil::class, 'getSaleEditableDocumentTypes']
            ),
            new \Twig_SimpleFilter(
                'sale_support_document_type',
                [SaleDocumentUtil::class, 'isSaleSupportsDocumentType']
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            // Renders the sale view
            new \Twig_SimpleFunction(
                'render_sale_view',
                [$this, 'renderSaleView'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            // Renders the sale transform button
            new \Twig_SimpleFunction(
                'sale_transform_btn',
                [$this, 'renderSaleTransformButton'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Returns whether the sale is in a stockable state.
     *
     * @param Common\SaleInterface $sale
     *
     * @return bool
     */
    public function isSaleStockableSale(Common\SaleInterface $sale)
    {
        if ($sale instanceof Order\OrderInterface) {
            return Order\OrderStates::isStockableState($sale->getState());
        }

        return false;
    }

    /**
     * Returns whether the sale is in a preparable state.
     *
     * @param Common\SaleInterface $sale
     *
     * @return bool
     */
    public function isSalePreparable(Common\SaleInterface $sale)
    {
        if ($sale instanceof Order\OrderInterface) {
            if ($sale->isReleased()) {
                return false;
            }

            return Shipment\ShipmentStates::isPreparableState($sale->getShipmentState());
        }

        return false;
    }

    /**
     * Returns whether the sale is in a preparation state.
     *
     * @param Common\SaleInterface $sale
     *
     * @return bool
     */
    public function isSalePreparing(Common\SaleInterface $sale)
    {
        if ($sale instanceof Order\OrderInterface) {
            return $sale->getShipmentState() === Shipment\ShipmentStates::STATE_PREPARATION;
        }

        return false;
    }

    /**
     * Returns whether or not the sale has at least one payment (which is not 'new').
     *
     * @param Common\SaleInterface $sale
     *
     * @return bool
     */
    public function isSaleWithPayment(Common\SaleInterface $sale)
    {
        if (!$sale instanceof Payment\PaymentSubjectInterface) {
            return false;
        }

        foreach ($sale->getPayments() as $payment) {
            if ($payment->getState() !== Payment\PaymentStates::STATE_NEW) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether or not the sale has at least one shipment (which is not 'new' or a return).
     *
     * @param Common\SaleInterface $sale
     *
     * @return bool
     */
    public function isSaleWithShipment(Common\SaleInterface $sale)
    {
        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return false;
        }

        foreach ($sale->getShipments() as $shipment) {
            if ($shipment->isReturn()) {
                continue;
            }

            if ($shipment->getState() !== Shipment\ShipmentStates::STATE_NEW) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether or not the sale has at least one return shipment (which is not 'new').
     *
     * @param Common\SaleInterface $sale
     *
     * @return bool
     */
    public function isSaleWithReturn(Common\SaleInterface $sale)
    {
        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return false;
        }

        foreach ($sale->getShipments() as $shipment) {
            if (!$shipment->isReturn()) {
                continue;
            }

            if ($shipment->getState() !== Shipment\ShipmentStates::STATE_NEW) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether or not the sale has at least one invoice (which is not a credit).
     *
     * @param Common\SaleInterface $sale
     *
     * @return bool
     */
    public function isSaleWithInvoice(Common\SaleInterface $sale)
    {
        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            return false;
        }

        foreach ($sale->getInvoices() as $invoice) {
            if (Invoice\InvoiceTypes::isInvoice($invoice)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether or not the sale has at least one credit invoice.
     *
     * @param Common\SaleInterface $sale
     *
     * @return bool
     */
    public function isSaleWithCredit(Common\SaleInterface $sale)
    {
        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            return false;
        }

        foreach ($sale->getInvoices() as $invoice) {
            if (Invoice\InvoiceTypes::isCredit($invoice)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether or not the sale has at least one attachment (which is not internal).
     *
     * @param Common\SaleInterface $sale
     *
     * @return bool
     */
    public function isSaleWithAttachment(Common\SaleInterface $sale)
    {
        foreach ($sale->getAttachments() as $attachment) {
            if (!$attachment->isInternal()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the sale's payments (which are not 'new').
     *
     * @param Common\SaleInterface $sale
     *
     * @return array
     */
    public function getSalePayments(Common\SaleInterface $sale)
    {
        if (!$sale instanceof Payment\PaymentSubjectInterface) {
            return [];
        }

        return $sale->getPayments()->filter(function (Payment\PaymentInterface $payment) {
            return $payment->getState() !== Payment\PaymentStates::STATE_NEW;
        })->toArray();
    }

    /**
     * Returns the sale's shipments (which are not 'new' or returns).
     *
     * @param Common\SaleInterface $sale
     *
     * @return array
     */
    public function getSaleShipments(Common\SaleInterface $sale)
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
     * @param Common\SaleInterface $sale
     *
     * @return array
     */
    public function getSaleReturns(Common\SaleInterface $sale)
    {
        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return [];
        }

        return $sale->getShipments()->filter(function (Shipment\ShipmentInterface $shipment) {
            return $shipment->isReturn() && $shipment->getState() !== Shipment\ShipmentStates::STATE_NEW;
        })->toArray();
    }

    /**
     * Returns the sale's invoices (which are not credits).
     *
     * @param Common\SaleInterface $sale
     *
     * @return array
     */
    public function getSaleInvoices(Common\SaleInterface $sale)
    {
        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            return [];
        }

        return $sale->getInvoices()->filter(function (Invoice\InvoiceInterface $invoice) {
            return Invoice\InvoiceTypes::isInvoice($invoice);
        })->toArray();
    }

    /**
     * Returns the sale's credits.
     *
     * @param Common\SaleInterface $sale
     *
     * @return array
     */
    public function getSaleCredits(Common\SaleInterface $sale)
    {
        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            return [];
        }

        return $sale->getInvoices()->filter(function (Invoice\InvoiceInterface $invoice) {
            return Invoice\InvoiceTypes::isCredit($invoice);
        })->toArray();
    }

    /**
     * Returns the sale's attachments (which are not internal).
     *
     * @param Common\SaleInterface $sale
     *
     * @return array
     */
    public function getSaleAttachments(Common\SaleInterface $sale)
    {
        return $sale->getAttachments()->filter(function (Common\AttachmentInterface $attachment) {
            return !$attachment->isInternal();
        })->toArray();
    }

    /**
     * Returns the sale shipment (net or ati regarding to the sale's context).
     *
     * @param Common\SaleInterface $sale
     *
     * @return float
     */
    public function getSaleShipmentAmount(Common\SaleInterface $sale)
    {
        $amount = $net = $sale->getShipmentAmount();

        if ($this->contextProvider->getContext($sale)->isAtiDisplayMode()) {
            foreach ($sale->getAdjustments(Common\AdjustmentTypes::TYPE_TAXATION) as $adjustment) {
                $amount += $net * $adjustment->getAmount() / 100;
            }
        }

        return $amount;
    }

    /**
     * Renders the sale view.
     *
     * @param \Twig_Environment $env
     * @param SaleView          $view
     * @param string            $template
     *
     * @return string
     */
    public function renderSaleView(\Twig_Environment $env, SaleView $view, $template = null)
    {
        if (empty($template)) {
            $template = $view->getTemplate();
        }

        return $env->load($template)->renderBlock('sale', ['view' => $view]);
    }

    /**
     * Renders the sale transform button.
     *
     * @param Common\SaleInterface $sale
     *
     * @return string
     */
    public function renderSaleTransformButton(Common\SaleInterface $sale)
    {
        $actions = [];

        if (empty($targets = Common\TransformationTargets::getTargetsForSale($sale))) {
            return '';
        }

        if ($sale instanceof CartInterface) {
            foreach ($targets as $target) {
                $actions['ekyna_commerce.' . $target . '.label.singular'] =
                    $this->urlGenerator->generate('ekyna_commerce_cart_admin_transform', [
                        'cartId' => $sale->getId(),
                        'target' => $target,
                    ]);
            }
        } elseif ($sale instanceof QuoteInterface) {
            foreach ($targets as $target) {
                $actions['ekyna_commerce.' . $target . '.label.singular'] =
                    $this->urlGenerator->generate('ekyna_commerce_quote_admin_transform', [
                        'quoteId' => $sale->getId(),
                        'target'  => $target,
                    ]);
            }
        } elseif ($sale instanceof Order\OrderInterface) {
            foreach ($targets as $target) {
                $actions['ekyna_commerce.' . $target . '.label.singular'] =
                    $this->urlGenerator->generate('ekyna_commerce_order_admin_transform', [
                        'orderId' => $sale->getId(),
                        'target'  => $target,
                    ]);
            }
        } else {
            throw new InvalidArgumentException("Unsupported sale type.");
        }

        return $this
            ->uiRenderer
            ->renderButtonDropdown('ekyna_core.button.transform', $actions);
    }
}
