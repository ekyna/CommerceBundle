<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\CoreBundle\Service\Ui\UiRenderer;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\View\SaleView;
use Ekyna\Component\Commerce\Common\View\ViewBuilder;
use Ekyna\Component\Commerce\Document\Util\DocumentUtil;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Order\Model as Order;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
     * @param ContextProviderInterface $contextProvider
     * @param ViewBuilder              $viewBuilder
     * @param UiRenderer               $uiRenderer
     * @param UrlGeneratorInterface    $urlGenerator
     */
    public function __construct(
        ContextProviderInterface $contextProvider,
        ViewBuilder $viewBuilder,
        UiRenderer $uiRenderer,
        UrlGeneratorInterface $urlGenerator
    ) {
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
            new TwigTest('sale', function($subject) {
                return $subject instanceof Common\SaleInterface;
            }),
            new TwigTest('sale_cart', function($subject) {
                return $subject instanceof CartInterface;
            }),
            new TwigTest('sale_quote', function($subject) {
                return $subject instanceof QuoteInterface;
            }),
            new TwigTest('sale_order', function($subject) {
                return $subject instanceof OrderInterface;
            }),
            new TwigTest(
                'sale_stockable_state',
                [$this, 'isSaleStockableSale']
            ),
            new TwigTest(
                'sale_preparable',
                [$this, 'isSalePreparable']
            ),
            new TwigTest(
                'sale_preparing',
                [$this, 'isSalePreparing']
            ),
            new TwigTest(
                'sale_with_payment',
                [$this, 'isSaleWithPayment']
            ),
            new TwigTest(
                'sale_with_refund',
                [$this, 'isSaleWithRefund']
            ),
            new TwigTest(
                'sale_with_shipment',
                [$this, 'isSaleWithShipment']
            ),
            new TwigTest(
                'sale_with_return',
                [$this, 'isSaleWithReturn']
            ),
            new TwigTest(
                'sale_with_invoice',
                [$this, 'isSaleWithInvoice']
            ),
            new TwigTest(
                'sale_with_credit',
                [$this, 'isSaleWithCredit']
            ),
            new TwigTest(
                'sale_with_attachment',
                [$this, 'isSaleWithAttachment']
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
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
                'sale_payments',
                [$this, 'getSalePayments']
            ),
            new TwigFilter(
                'sale_attachments',
                [$this, 'getSaleAttachments']
            ),
            new TwigFilter(
                'sale_shipment_amount',
                [$this, 'getSaleShipmentAmount']
            ),
            // Builds the sale view form the sale
            new TwigFilter(
                'sale_view',
                [$this->viewBuilder, 'buildSaleView']
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

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            // Renders the sale view
            new TwigFunction(
                'render_sale_view',
                [$this, 'renderSaleView'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            // Renders the sale transform button
            new TwigFunction(
                'sale_transform_btn',
                [$this, 'renderSaleTransformButton'],
                ['is_safe' => ['html']]
            ),
            // Renders the sale duplicate button
            new TwigFunction(
                'sale_duplicate_btn',
                [$this, 'renderSaleDuplicateButton'],
                ['is_safe' => ['html']]
            ),
            // Renders the sale export button
            new TwigFunction(
                'sale_export_btn',
                [$this, 'renderSaleExportButton'],
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

        foreach ($sale->getPayments(true) as $payment) {
            if ($payment->getState() !== Payment\PaymentStates::STATE_NEW) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether or not the sale has at least one refund (which is not 'new').
     *
     * @param Common\SaleInterface $sale
     *
     * @return bool
     */
    public function isSaleWithRefund(Common\SaleInterface $sale)
    {
        if (!$sale instanceof Payment\PaymentSubjectInterface) {
            return false;
        }

        foreach ($sale->getPayments(false) as $payment) {
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

        foreach ($sale->getShipments(true) as $shipment) {
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

        foreach ($sale->getShipments(false) as $shipment) {
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

        return !$sale->getInvoices(true)->isEmpty();
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

        return !$sale->getInvoices(false)->isEmpty();
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
    public function renderSaleDuplicateButton(Common\SaleInterface $sale)
    {
        return $this->renderSaleOperationButton($sale, 'duplicate');
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
        return $this->renderSaleOperationButton($sale, 'transform');
    }

    /**
     * Renders the sale export button.
     *
     * @param Common\SaleInterface $sale
     *
     * @return string
     */
    public function renderSaleExportButton(Common\SaleInterface $sale)
    {
        $actions = [];

        $formats = ['CSV' => 'csv', 'Excel' => 'xls'];

        if ($sale instanceof CartInterface) {
            $type = 'cart';
        } elseif ($sale instanceof QuoteInterface) {
            $type = 'quote';
        } elseif ($sale instanceof Order\OrderInterface) {
            $type = 'order';
        } else {
            throw new InvalidArgumentException("Unsupported sale type.");
        }

        foreach ($formats as $name => $format) {
            /** @noinspection PhpRouteMissingInspection */
            $actions[$name] =
                $this->urlGenerator->generate("ekyna_commerce_{$type}_admin_export", [
                    "{$type}Id" => $sale->getId(),
                    '_format'   => $format,
                ]);
        }

        return $this
            ->uiRenderer
            ->renderDropdown($actions, [
                'label' => 'ekyna_core.button.export',
                'icon'  => 'download',
            ]);
    }

    /**
     * Renders the sale operation button.
     *
     * @param Common\SaleInterface $sale
     * @param string               $operation
     *
     * @return string
     */
    private function renderSaleOperationButton(Common\SaleInterface $sale, string $operation)
    {
        $actions = [];

        if (empty($targets = Common\TransformationTargets::getTargetsForSale($sale, $operation === 'duplicate'))) {
            return '';
        }

        if ($sale instanceof CartInterface) {
            foreach ($targets as $target) {
                $actions['ekyna_commerce.' . $target . '.label.singular'] =
                    $this->urlGenerator->generate('ekyna_commerce_cart_admin_' . $operation, [
                        'cartId' => $sale->getId(),
                        'target' => $target,
                    ]);
            }
        } elseif ($sale instanceof QuoteInterface) {
            foreach ($targets as $target) {
                $actions['ekyna_commerce.' . $target . '.label.singular'] =
                    $this->urlGenerator->generate('ekyna_commerce_quote_admin_' . $operation, [
                        'quoteId' => $sale->getId(),
                        'target'  => $target,
                    ]);
            }
        } elseif ($sale instanceof Order\OrderInterface) {
            foreach ($targets as $target) {
                $actions['ekyna_commerce.' . $target . '.label.singular'] =
                    $this->urlGenerator->generate('ekyna_commerce_order_admin_' . $operation, [
                        'orderId' => $sale->getId(),
                        'target'  => $target,
                    ]);
            }
        } else {
            throw new InvalidArgumentException("Unsupported sale type.");
        }

        return $this
            ->uiRenderer
            ->renderDropdown($actions, [
                'label'   => 'ekyna_core.button.' . $operation,
                'icon'    => $operation === 'duplicate' ? 'clone' : 'magic',
                'fa_icon' => true,
            ]);
    }
}
