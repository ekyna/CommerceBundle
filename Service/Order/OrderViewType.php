<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Order;

use Ekyna\Bundle\CommerceBundle\Action\Admin;
use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Bundle\CommerceBundle\Service\Stock\StockRenderer;
use Ekyna\Component\Commerce\Common\Helper\SaleHelper;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\View;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Order\Model as Order;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignmentInterface;
use Ekyna\Component\Commerce\Stock\Prioritizer\OrderPrioritizeCheckerInterface;
use Exception;

use function sprintf;

/**
 * Class OrderViewType
 * @package Ekyna\Bundle\CommerceBundle\Service\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderViewType extends AbstractViewType
{
    private readonly OrderPrioritizeCheckerInterface    $prioritizeChecker;
    private readonly StockRenderer                      $stockRenderer;
    private readonly InvoiceSubjectCalculatorInterface  $invoiceCalculator;
    private readonly ShipmentSubjectCalculatorInterface $shipmentCalculator;
    private readonly ShipmentPriceResolverInterface     $shipmentPriceResolver;

    public function setPrioritizeChecker(OrderPrioritizeCheckerInterface $prioritizeChecker): void
    {
        $this->prioritizeChecker = $prioritizeChecker;
    }

    public function setStockRenderer(StockRenderer $renderer): void
    {
        $this->stockRenderer = $renderer;
    }

    public function setInvoiceCalculator(InvoiceSubjectCalculatorInterface $calculator): void
    {
        $this->invoiceCalculator = $calculator;
    }

    public function setShipmentSubjectCalculator(ShipmentSubjectCalculatorInterface $calculator): void
    {
        $this->shipmentCalculator = $calculator;
    }

    public function setShipmentPriceResolver(ShipmentPriceResolverInterface $resolver): void
    {
        $this->shipmentPriceResolver = $resolver;
    }

    public function buildSaleView(Common\SaleInterface $sale, View\SaleView $view, array $options): void
    {
        if (!$options['editable'] || !$options['private']) {
            return;
        }

        $view->vars['attr']['data-type'] = 'order';

        if ($this->prioritizeChecker->check($sale)) {
            // Prioritize button
            $prioritizePath = $this->resourceUrl($sale, Admin\Order\PrioritizeAction::class);
            $view->addButton('items.prioritize', new View\Button(
                $prioritizePath,
                $this->trans('button.prioritize', [], 'EkynaCommerce'),
                'fa fa-level-up', [
                    'id'      => 'order_prioritize',
                    'title'   => $this->trans('button.prioritize', [], 'EkynaCommerce'),
                    'class'   => 'btn btn-sm btn-warning',
                    'confirm' => $this->trans('sale.confirm.prioritize', [], 'EkynaCommerce'),
                ]
            ));
        }

        // Check items button
        $checkItemsPath = $this->resourceUrl($sale, Admin\Sale\CheckItemsAction::class);
        $view->addButton('items.check', new View\Button(
            $checkItemsPath,
            $this->trans('sale.button.check_items', [], 'EkynaCommerce'),
            'fa fa-check-circle-o', [
                'id'    => 'order_check_items',
                'title' => $this->trans('sale.button.check_items', [], 'EkynaCommerce'),
                'class' => 'btn btn-sm btn-default',
            ]
        ));

        // Refresh button
        $refreshPath = $this->resourceUrl($sale, Admin\Sale\RefreshAction::class);
        $view->addButton('refresh', new View\Button(
            $refreshPath,
            $this->trans('button.refresh', [], 'EkynaUi'),
            'fa fa-refresh', [
                'id'            => 'order_refresh',
                'title'         => $this->trans('button.refresh', [], 'EkynaUi'),
                'class'         => 'btn btn-sm btn-default',
                'data-sale-xhr' => 'get',
            ]
        ));

        // Add item button
        $addItemPath = $this->resourceUrl('ekyna_commerce.order_item', Admin\Sale\Item\AddAction::class, [
            'orderId' => $sale->getId(),
        ]);
        $view->addButton('items.add', new View\Button(
            $addItemPath,
            $this->trans('sale.button.item.add', [], 'EkynaCommerce'),
            'fa fa-plus', [
                'id'              => 'order_item_add',
                'title'           => $this->trans('sale.button.item.add', [], 'EkynaCommerce'),
                'class'           => 'btn btn-sm btn-primary',
                'data-sale-modal' => null,
            ]
        ));

        // New item button
        $newItemPath = $this->resourceUrl('ekyna_commerce.order_item', Admin\Sale\Item\CreateAction::class, [
            'orderId' => $sale->getId(),
        ]);
        $view->addButton('items.create', new View\Button(
            $newItemPath,
            $this->trans('sale.button.item.new', [], 'EkynaCommerce'),
            'fa fa-plus', [
                'id'              => 'order_item_new',
                'title'           => $this->trans('sale.button.item.new', [], 'EkynaCommerce'),
                'class'           => 'btn btn-sm btn-default',
                'data-sale-modal' => null,
            ]
        ));

        // New adjustment button
        $newAdjustmentPath = $this->resourceUrl('ekyna_commerce.order_adjustment',
            Admin\Sale\Adjustment\CreateAction::class,
            [
                'orderId' => $sale->getId(),
            ]);
        $view->addButton('adjustments.create', new View\Button(
            $newAdjustmentPath,
            $this->trans('sale.button.adjustment.new', [], 'EkynaCommerce'),
            'fa fa-plus', [
                'id'              => 'order_adjustment_new',
                'title'           => $this->trans('sale.button.adjustment.new', [], 'EkynaCommerce'),
                'class'           => 'btn btn-sm btn-default',
                'data-sale-modal' => null,
            ]
        ));
    }

    public function buildItemView(Common\SaleItemInterface $item, View\LineView $view, array $options): void
    {
        if (!$item instanceof Order\OrderItemInterface) {
            throw new Exception('Unexpected sale item type.');
        }

        if (!$options['editable'] || !$options['private']) {
            return;
        }

        $this->buildShipmentAndInvoiceComment($item, $view);

        $sale = $item->getRootSale();

        // Manual adjustments
        if (!$item->getSubjectIdentity()->hasIdentity()
            || (
                !$sale->isAutoDiscount() && !$sale->isSample()
                && !($item->isPrivate() || ($item->isCompound() && !$item->hasPrivateChildren()))
            )
        ) {
            $adjustment = current($item->getAdjustments(Common\AdjustmentTypes::TYPE_DISCOUNT)->toArray());
            if (false !== $adjustment) {
                $editPath = $this->resourceUrl($adjustment, Admin\Sale\Adjustment\UpdateAction::class);
                $view->addAction('adjustment.update', new View\Action($editPath, 'fa fa-percent', [
                    'title'           => $this->trans('sale.button.adjustment.edit', [], 'EkynaCommerce'),
                    'class'           => 'text-warning',
                    'data-sale-modal' => null,
                ]));

                $removePath = $this->resourceUrl($adjustment, Admin\Sale\Adjustment\DeleteAction::class);
                $view->addAction('adjustment.delete', new View\Action($removePath, 'fa fa-percent', [
                    'title'           => $this->trans('sale.button.adjustment.remove', [], 'EkynaCommerce'),
                    //'confirm'       => $this->trans('sale.confirm.adjustment.remove', [], 'EkynaCommerce'),
                    //'data-sale-xhr' => null,
                    'data-sale-modal' => null,
                    'class'           => 'text-danger',
                ]));
            } else {
                // New adjustment button
                $newAdjustmentPath = $this->resourceUrl(
                    'ekyna_commerce.order_item_adjustment',
                    Admin\Sale\Adjustment\CreateAction::class,
                    [
                        'orderId'     => $item->getRootSale()->getId(),
                        'orderItemId' => $item->getId(),
                    ]
                );
                $view->addAction('adjustment.create', new View\Action($newAdjustmentPath, 'fa fa-percent', [
                    'title'           => $this->trans('sale.button.adjustment.new', [], 'EkynaCommerce'),
                    'data-sale-modal' => null,
                    'class'           => 'text-success',
                ]));
            }
        }

        // Edit action
        // if (!$locked) {
        $editPath = $this->resourceUrl($item, Admin\Sale\Item\UpdateAction::class);
        $view->addAction('update', new View\Action($editPath, 'fa fa-pencil', [
            'title'           => $this->trans('sale.button.item.edit', [], 'EkynaCommerce'),
            'data-sale-modal' => null,
            'class'           => 'text-warning',
        ]));
        //}

        // Prioritize
        if ($this->prioritizeChecker->checkItem($item)) {
            $prioritizePath = $this->resourceUrl($item, Admin\Order\Item\PrioritizeAction::class);
            $view->addAction('prioritize', new View\Action($prioritizePath, 'fa fa-level-up', [
                'title'           => $this->trans('button.prioritize', [], 'EkynaCommerce'),
                'data-sale-modal' => null,
                'class'           => 'text-primary',
            ]));
        }

        // Information
        if (!empty($assignments = $item->getStockAssignments()->toArray())) {
            $view->vars['information'] = $this->stockRenderer->renderStockAssignments($assignments, [
                'prefix' => $view->id . '_su',
                'class'  => 'table-alt',
            ]);

            $class = 'text-muted';
            /** @var AssignmentInterface $assignment */
            foreach ($assignments as $assignment) {
                if (!$assignment->isFullyShipped() && !$assignment->isFullyShippable()) {
                    $class = 'text-danger';
                }
            }

            $view->addAction('toggle_assignments', new View\Action('javascript: void(0)', 'fa fa-tasks', [
                'title'               => $this->trans('sale.button.item.information', [], 'EkynaCommerce'),
                'data-toggle-details' => $view->id . '_information',
                'class'               => $class,
            ]));
        }

        if ($item->hasParent()) {
            return;
        }

        // Move up
        if (0 < $item->getPosition()) {
            $moveUpPath = $this->resourceUrl($item, Admin\Sale\Item\MoveUpAction::class);
            $view->addAction('move_up', new View\Action($moveUpPath, 'fa fa-arrow-up', [
                'title'         => $this->trans('button.move_up', [], 'EkynaUi'),
                'data-sale-xhr' => 'get',
                'class'         => 'text-muted',
            ]));
        }

        // Move down
        if (!$item->isLast()) {
            $moveDownPath = $this->resourceUrl($item, Admin\Sale\Item\MoveDownAction::class);
            $view->addAction('move_down', new View\Action($moveDownPath, 'fa fa-arrow-down', [
                'title'         => $this->trans('button.move_down', [], 'EkynaUi'),
                'data-sale-xhr' => 'get',
                'class'         => 'text-muted',
            ]));
        }

        if ($item->isImmutable()
            || $this->invoiceCalculator->isInvoiced($item)
            || $this->shipmentCalculator->isShipped($item)) {
            return;
        }

        // Configure action
        if ($item->isConfigurable()) {
            $configurePath = $this->resourceUrl($item, Admin\Sale\Item\ConfigureAction::class);
            $view->addAction('configure', new View\Action($configurePath, 'fa fa-cog', [
                'title'           => $this->trans('sale.button.item.configure', [], 'EkynaCommerce'),
                'data-sale-modal' => null,
                'class'           => 'text-primary',
            ]));
        }

        if (!SaleHelper::isSaleWithPayment($sale)) {
            // Resolve price action
            $pricePath = $this->resourceUrl($item, Admin\Sale\Item\ResolvePriceAction::class);
            $view->addAction('update_price', new View\Action($pricePath, 'fa fa-euro', [
                'title'         => $this->trans('sale.button.item.resolve_price', [], 'EkynaCommerce'),
                'data-sale-xhr' => null,
                'class'         => 'text-primary',
            ]));

            // Sync with subject
            if ($item->getSubjectIdentity()->hasIdentity()) {
                $syncPath = $this->resourceUrl($item, Admin\Order\Item\SyncSubjectAction::class);
                $view->addAction('sync_subject', new View\Action($syncPath, 'fa fa-cube', [
                    'title'         => $this->trans('sale.button.item.sync_subject', [], 'EkynaCommerce'),
                    'confirm'       => $this->trans('sale.confirm.item.sync_subject', [], 'EkynaCommerce'),
                    'data-sale-xhr' => null,
                    'class'         => 'text-warning',
                ]));
            }
        }

        // Remove action
        $removePath = $this->resourceUrl($item, Admin\Sale\Item\DeleteAction::class);
        $view->addAction('delete', new View\Action($removePath, 'fa fa-remove', [
            'title'           => $this->trans('sale.button.item.remove', [], 'EkynaCommerce'),
            //'confirm'       => $this->trans('sale.confirm.item.remove', [], 'EkynaCommerce'),
            //'data-sale-xhr' => null,
            'data-sale-modal' => null,
            'class'           => 'text-danger',
        ]));
    }

    private function buildShipmentAndInvoiceComment(Common\SaleItemInterface $item, View\LineView $view): void
    {
        if ($item->isCompound() && !$item->hasPrivateChildren()) {
            return;
        }

        $lines = [];
        $lines['field.available'] = $this->formatter->number(
            $this->shipmentCalculator->calculateAvailableQuantity($item)
        );

        $shipped = $this->shipmentCalculator->calculateShippedQuantity($item);
        $returned = $this->shipmentCalculator->calculateReturnedQuantity($item);

        if (0 < $returned) {
            $shipment = sprintf('%s (-%s)',
                $this->formatter->number($shipped),
                $this->formatter->number($returned));
        } else {
            $shipment = $this->formatter->number($shipped);
        }

        $lines['field.shipped'] = $shipment;

        $sale = $item->getRootSale();
        if (!$sale->isSample()) {
            $invoiced = $this->invoiceCalculator->calculateInvoicedQuantity($item);
            $credited = $this->invoiceCalculator->calculateCreditedQuantity($item, null, false);

            if (0 < $credited) {
                $invoice = sprintf(
                    '%s (-%s)',
                    $this->formatter->number($invoiced),
                    $this->formatter->number($credited)
                );
            } else {
                $invoice = $this->formatter->number($invoiced);
            }

            $lines['field.invoiced'] = $invoice;

            $balance = $shipped->sub($returned)->sub($invoiced)->add($credited);

            if (!$balance->isZero()) {
                $lines['field.balance'] = sprintf(
                    '<strong style="color:red">%s</strong>',
                    $this->formatter->number($balance)
                );
            }
        }

        $comment = '<dl class="dl-horizontal" style="font-size:13px;">';
        foreach ($lines as $label => $value) {
            $comment .= sprintf('<dt>%s</dt><dd>%s</dd>', $this->trans($label, [], 'EkynaCommerce'), $value);
        }
        $comment .= '</dl>';

        $view->addComment('shipment_invoice_summary', new View\Comment($comment));
    }

    public function buildAdjustmentView(
        Common\AdjustmentInterface $adjustment,
        View\LineView              $view,
        array                      $options
    ): void {
        if ($adjustment->isImmutable() || (!$options['editable']) || (!$options['private'])) {
            return;
        }

        // Edit action
        $editPath = $this->resourceUrl($adjustment, Admin\Sale\Adjustment\UpdateAction::class);
        $view->addAction('update', new View\Action($editPath, 'fa fa-pencil', [
            'title'           => $this->trans('sale.button.adjustment.edit', [], 'EkynaCommerce'),
            'data-sale-modal' => null,
            'class'           => 'text-warning',
        ]));

        // Remove action
        if ($adjustment instanceof Common\SaleAdjustmentInterface) {
            // Not if invoiced
            if ($this->invoiceCalculator->isInvoiced($adjustment)) {
                return;
            }
        }
        $removePath = $this->resourceUrl($adjustment, Admin\Sale\Adjustment\DeleteAction::class);
        $view->addAction('delete', new View\Action($removePath, 'fa fa-remove', [
            'title'           => $this->trans('sale.button.adjustment.remove', [], 'EkynaCommerce'),
            //'confirm'       => $this->trans('sale.confirm.adjustment.remove', [], 'EkynaCommerce'),
            //'data-sale-xhr' => null,
            'data-sale-modal' => null,
            'class'           => 'text-danger',
        ]));
    }

    public function buildShipmentView(Common\SaleInterface $sale, View\LineView $view, array $options): void
    {
        if (!$options['editable'] || !$options['private']) {
            return;
        }

        $this->setShipmentViewClass($sale, $view);

        if ($sale->isAutoShipping()) {
            return;
        }

        $editPath = $this->resourceUrl($sale, Admin\Sale\UpdateShipmentAction::class);
        $view->addAction('shipment.update', new View\Action($editPath, 'fa fa-pencil', [
            'title'           => $this->trans('sale.button.shipment.edit', [], 'EkynaCommerce'),
            'data-sale-modal' => null,
            'class'           => 'text-warning',
        ]));
    }

    /**
     * Sets the shipment line view's class.
     *
     * @param Common\SaleInterface $sale
     * @param View\LineView        $view
     */
    private function setShipmentViewClass(Common\SaleInterface $sale, View\LineView $view): void
    {
        if (null === $p = $this->shipmentPriceResolver->getPriceBySale($sale)) {
            $view->addClass('danger');

            return;
        }

        if (!$p->getPrice()->equals($sale->getShipmentAmount())) {
            $view->addClass('warning');
        }
    }

    public function supportsSale(Common\SaleInterface $sale): bool
    {
        return $sale instanceof Order\OrderInterface;
    }

    public function getName(): string
    {
        return 'ekyna_commerce_order';
    }
}
