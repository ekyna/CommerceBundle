<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Order;

use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Bundle\CommerceBundle\Service\Stock\StockRenderer;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\View;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCalculatorInterface;
use Ekyna\Component\Commerce\Order\Model as Order;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCalculatorInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Prioritizer\StockPrioritizerInterface;

/**
 * Class OrderViewType
 * @package Ekyna\Bundle\CommerceBundle\Service\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderViewType extends AbstractViewType
{
    /**
     * @var StockPrioritizerInterface
     */
    private $stockPrioritizer;

    /**
     * @var StockRenderer
     */
    private $stockRenderer;

    /**
     * @var InvoiceCalculatorInterface
     */
    private $invoiceCalculator;

    /**
     * @var ShipmentCalculatorInterface
     */
    private $shipmentCalculator;


    /**
     * Sets the stock prioritizer.
     *
     * @param StockPrioritizerInterface $prioritizer
     */
    public function setStockPrioritizer(StockPrioritizerInterface $prioritizer)
    {
        $this->stockPrioritizer = $prioritizer;
    }

    /**
     * Sets the stock renderer.
     *
     * @param StockRenderer $renderer
     */
    public function setStockRenderer(StockRenderer $renderer)
    {
        $this->stockRenderer = $renderer;
    }

    /**
     * Sets the invoice calculator.
     *
     * @param InvoiceCalculatorInterface $calculator
     */
    public function setInvoiceCalculator($calculator)
    {
        $this->invoiceCalculator = $calculator;
    }

    /**
     * Sets the shipment calculator.
     *
     * @param ShipmentCalculatorInterface $calculator
     */
    public function setShipmentCalculator($calculator)
    {
        $this->shipmentCalculator = $calculator;
    }

    /**
     * @inheritdoc
     */
    public function buildSaleView(Common\SaleInterface $sale, View\SaleView $view, array $options)
    {
        if (!$options['editable'] || !$options['private']) {
            return;
        }

        if ($this->stockPrioritizer->canPrioritizeSale($sale)) {
            // Prioritize button
            $prioritizePath = $this->generateUrl('ekyna_commerce_order_admin_prioritize', [
                'orderId' => $sale->getId(),
            ]);
            $view->addButton(new View\Button(
                $prioritizePath,
                $this->trans('ekyna_commerce.sale.button.prioritize'),
                'fa fa-level-up', [
                    'id'      => 'order_prioritize',
                    'title'   => $this->trans('ekyna_commerce.sale.button.prioritize'),
                    'class'   => 'btn btn-sm btn-warning',
                    'confirm' => $this->trans('ekyna_commerce.sale.confirm.prioritize'),
                ]
            ));
        }

        // Refresh button
        $refreshPath = $this->generateUrl('ekyna_commerce_order_admin_refresh', [
            'orderId' => $sale->getId(),
        ]);
        $view->addButton(new View\Button(
            $refreshPath,
            $this->trans('ekyna_commerce.sale.button.refresh'),
            'fa fa-refresh', [
                'id'            => 'order_refresh',
                'title'         => $this->trans('ekyna_commerce.sale.button.refresh'),
                'class'         => 'btn btn-sm btn-default',
                'data-sale-xhr' => 'get',
            ]
        ));

        // Add item button
        $addItemPath = $this->generateUrl('ekyna_commerce_order_item_admin_add', [
            'orderId' => $sale->getId(),
        ]);
        $view->addButton(new View\Button(
            $addItemPath,
            $this->trans('ekyna_commerce.sale.button.item.add'),
            'fa fa-plus', [
                'id'              => 'order_item_add',
                'title'           => 'ekyna_commerce.sale.button.item.add',
                'class'           => 'btn btn-sm btn-primary',
                'data-sale-modal' => null,
            ]
        ));

        // New item button
        $newItemPath = $this->generateUrl('ekyna_commerce_order_item_admin_new', [
            'orderId' => $sale->getId(),
        ]);
        $view->addButton(new View\Button(
            $newItemPath,
            $this->trans('ekyna_commerce.sale.button.item.new'),
            'fa fa-plus', [
                'id'              => 'order_item_new',
                'title'           => $this->trans('ekyna_commerce.sale.button.item.new'),
                'class'           => 'btn btn-sm btn-default',
                'data-sale-modal' => null,
            ]
        ));

        // New adjustment button
        $newAdjustmentPath = $this->generateUrl('ekyna_commerce_order_adjustment_admin_new', [
            'orderId' => $sale->getId(),
        ]);
        $view->addButton(new View\Button(
            $newAdjustmentPath,
            $this->trans('ekyna_commerce.sale.button.adjustment.new'),
            'fa fa-plus', [
                'id'              => 'order_adjustment_new',
                'title'           => $this->trans('ekyna_commerce.sale.button.adjustment.new'),
                'class'           => 'btn btn-sm btn-default',
                'data-sale-modal' => null,
            ]
        ));
    }

    /**
     * @inheritdoc
     */
    public function buildItemView(Common\SaleItemInterface $item, View\LineView $view, array $options)
    {
        if (!$options['editable'] || !$options['private']) {
            return;
        }

        $sale = $item->getSale();

        // Information
        if (!$item instanceof Order\OrderItemInterface) {
            throw new \Exception("Unexpected sale item type.");
        }
        if (!empty($assignments = $item->getStockAssignments()->toArray())) {
            $view->vars['information'] = $this->stockRenderer->renderStockAssignments($assignments, [
                'prefix' => $view->getId() . '_su',
                'class'  => 'table-alt',
            ]);

            $class = 'text-muted';
            /** @var StockAssignmentInterface $assignment */
            foreach ($assignments as $assignment) {
                if (!$assignment->isFullyShipped() && !$assignment->isFullyShippable()) {
                    $class = 'text-danger';
                }
            }

            $view->addAction(new View\Action('javascript: void(0)', 'fa fa-tasks', [
                'title'               => $this->trans('ekyna_commerce.sale.button.item.information'),
                'data-toggle-details' => $view->getId() . '_information',
                'class'               => $class,
            ]));
        }

        // Popover
        $popover = '';
        if (!($item->isCompound() && !$item->hasPrivateChildren())) {
            $lines = [];

            $shipped = $this->shipmentCalculator->calculateShippedQuantity($item);
            $returned = $this->shipmentCalculator->calculateReturnedQuantity($item);

            if (0 < $returned) {
                $shipped = sprintf('%s (-%s)', $this->formatter->number($shipped), $this->formatter->number($returned));
            } else {
                $shipped = $this->formatter->number($shipped);
            }

            $lines['ekyna_commerce.sale_item.field.shipped'] = $shipped;

            $lines['ekyna_commerce.sale_item.field.available'] = $this->formatter->number(
                $this->shipmentCalculator->calculateAvailableQuantity($item)
            );

            if (!$sale->isSample()) {
                $invoiced = $this->invoiceCalculator->calculateInvoicedQuantity($item);
                $credited = $this->invoiceCalculator->calculateCreditedQuantity($item);

                if (0 < $credited) {
                    $invoiced = sprintf('%s (-%s)', $this->formatter->number($invoiced), $this->formatter->number($credited));
                } else {
                    $invoiced = $this->formatter->number($invoiced);
                }

                $lines['ekyna_commerce.sale_item.field.invoiced'] = $invoiced;
            }

            $popover = '<dl class="dl-horizontal" style="font-size:13px">';
            foreach ($lines as $label => $value) {
                $popover .= sprintf('<dt>%s</dt><dd>%s</dd>', $this->trans($label), $value);
            }
            $popover .= '</dl>';
        }
        if (!empty($popover)) {
            $view->vars['attr'] = array_replace($view->vars['attr'], [
                'data-toggle'    => 'popover',
                'data-content'   => $popover,
            ]);
        }

        // Manual adjustments
        if (
            !$sale->isAutoDiscount() && !$sale->isSample() &&
            !($item->isPrivate() || ($item->isCompound() && !$item->hasPrivateChildren()))
        ) {
            $adjustment = current($item->getAdjustments(Common\AdjustmentTypes::TYPE_DISCOUNT)->toArray());
            if (false !== $adjustment) {
                $routePrefix = 'ekyna_commerce_order_item_adjustment_admin_';
                $parameters = [
                    'orderId'               => $item->getSale()->getId(),
                    'orderItemId'           => $item->getId(),
                    'orderItemAdjustmentId' => $adjustment->getId(),
                ];

                $editPath = $this->generateUrl($routePrefix . 'edit', $parameters);
                $view->addAction(new View\Action($editPath, 'fa fa-percent', [
                    'title'           => $this->trans('ekyna_commerce.sale.button.adjustment.edit'),
                    'class'           => 'text-warning',
                    'data-sale-modal' => null,
                ]));

                $removePath = $this->generateUrl($routePrefix . 'remove', $parameters);
                $view->addAction(new View\Action($removePath, 'fa fa-percent', [
                    'title'         => $this->trans('ekyna_commerce.sale.button.adjustment.remove'),
                    'confirm'       => $this->trans('ekyna_commerce.sale.confirm.adjustment.remove'),
                    'class'         => 'text-danger',
                    'data-sale-xhr' => null,
                ]));
            } else {
                // New adjustment button
                $newAdjustmentPath = $this->generateUrl('ekyna_commerce_order_item_adjustment_admin_new', [
                    'orderId'     => $item->getSale()->getId(),
                    'orderItemId' => $item->getId(),
                ]);
                $view->addAction(new View\Action($newAdjustmentPath, 'fa fa-percent', [
                    'title'           => $this->trans('ekyna_commerce.sale.button.adjustment.new'),
                    'data-sale-modal' => null,
                    'class'           => 'text-success',
                ]));
            }
        }

        if (!$item->isImmutable() && !$item->getParent()) {
            // Configure action
            if ($item->isConfigurable()) {
                $configurePath = $this->generateUrl('ekyna_commerce_order_item_admin_configure', [
                    'orderId'     => $item->getSale()->getId(),
                    'orderItemId' => $item->getId(),
                ]);
                $view->addAction(new View\Action($configurePath, 'fa fa-cog', [
                    'title'           => $this->trans('ekyna_commerce.sale.button.item.configure'),
                    'data-sale-modal' => null,
                    'class'           => 'text-primary',
                ]));
            }
        }

        // Edit action
        //if (!$item->isCompound()) {
        $editPath = $this->generateUrl('ekyna_commerce_order_item_admin_edit', [
            'orderId'     => $item->getSale()->getId(),
            'orderItemId' => $item->getId(),
        ]);
        $view->addAction(new View\Action($editPath, 'fa fa-pencil', [
            'title'           => $this->trans('ekyna_commerce.sale.button.item.edit'),
            'data-sale-modal' => null,
            'class'           => 'text-warning',
        ]));
        //}

        // Abort if has parent
        if ($item->getParent()) {
            return;
        }

        // Move up
        if (0 < $item->getPosition()) {
            $moveUpPath = $this->generateUrl('ekyna_commerce_order_item_admin_move_up', [
                'orderId'     => $item->getSale()->getId(),
                'orderItemId' => $item->getId(),
            ]);
            $view->addAction(new View\Action($moveUpPath, 'fa fa-arrow-up', [
                'title'         => $this->trans('ekyna_core.button.move_up'),
                'data-sale-xhr' => 'get',
                'class'         => 'text-muted',
            ]));
        }

        // Move down
        if (!$item->isLast()) {
            $moveUpPath = $this->generateUrl('ekyna_commerce_order_item_admin_move_down', [
                'orderId'     => $item->getSale()->getId(),
                'orderItemId' => $item->getId(),
            ]);
            $view->addAction(new View\Action($moveUpPath, 'fa fa-arrow-down', [
                'title'         => $this->trans('ekyna_core.button.move_down'),
                'data-sale-xhr' => 'get',
                'class'         => 'text-muted',
            ]));
        }

        // Abort if immutable
        if ($item->isImmutable()) {
            return;
        }
        // Abort if invoiced
        if ($this->invoiceCalculator->isInvoiced($item)) {
            return;
        }
        // Abort if shipped
        if ($this->shipmentCalculator->isShipped($item)) {
            return;
        }

        // Remove action
        $removePath = $this->generateUrl('ekyna_commerce_order_item_admin_remove', [
            'orderId'     => $item->getSale()->getId(),
            'orderItemId' => $item->getId(),
        ]);
        $view->addAction(new View\Action($removePath, 'fa fa-remove', [
            'title'         => $this->trans('ekyna_commerce.sale.button.item.remove'),
            'confirm'       => $this->trans('ekyna_commerce.sale.confirm.item.remove'),
            'data-sale-xhr' => null,
            'class'         => 'text-danger',
        ]));
    }

    /**
     * @inheritdoc
     */
    public function buildAdjustmentView(Common\AdjustmentInterface $adjustment, View\LineView $view, array $options)
    {
        if ($adjustment->isImmutable() || (!$options['editable']) || (!$options['private'])) {
            return;
        }

        $adjustable = $adjustment->getAdjustable();
        if ($adjustable instanceof Order\OrderInterface) {
            $routePrefix = 'ekyna_commerce_order_adjustment_admin_';
            $parameters = [
                'orderId'           => $adjustable->getId(),
                'orderAdjustmentId' => $adjustment->getId(),
            ];
        } else {
            throw new InvalidArgumentException("Unexpected adjustable.");
        }

        // Edit action
        $editPath = $this->generateUrl($routePrefix . 'edit', $parameters);
        $view->addAction(new View\Action($editPath, 'fa fa-pencil', [
            'title'           => $this->trans('ekyna_commerce.sale.button.adjustment.edit'),
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
        $removePath = $this->generateUrl($routePrefix . 'remove', $parameters);
        $view->addAction(new View\Action($removePath, 'fa fa-remove', [
            'title'         => $this->trans('ekyna_commerce.sale.button.adjustment.remove'),
            'confirm'       => $this->trans('ekyna_commerce.sale.confirm.adjustment.remove'),
            'data-sale-xhr' => null,
            'class'         => 'text-danger',
        ]));
    }

    /**
     * @inheritdoc
     */
    public function buildShipmentView(Common\SaleInterface $sale, View\LineView $view, array $options)
    {
        if (!$options['editable'] || !$options['private']) {
            return;
        }

        $editPath = $this->generateUrl('ekyna_commerce_order_admin_edit_shipment', [
            'orderId' => $sale->getId(),
        ]);
        $view->addAction(new View\Action($editPath, 'fa fa-pencil', [
            'title'           => $this->trans('ekyna_commerce.sale.button.shipment.edit'),
            'data-sale-modal' => null,
            'class'           => 'text-warning',
        ]));
    }

    /**
     * @inheritdoc
     */
    public function supportsSale(Common\SaleInterface $sale)
    {
        return $sale instanceof Order\OrderInterface;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'ekyna_commerce_order';
    }
}
