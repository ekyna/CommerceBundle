<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Order;

use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Bundle\CommerceBundle\Service\Stock\StockRenderer;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\View;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model as Order;

/**
 * Class OrderViewType
 * @package Ekyna\Bundle\CommerceBundle\Service\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderViewType extends AbstractViewType
{
    /**
     * @var StockRenderer
     */
    private $stockRenderer;


    /**
     * Sets the stockRenderer.
     *
     * @param StockRenderer $stockRenderer
     */
    public function setStockRenderer($stockRenderer)
    {
        $this->stockRenderer = $stockRenderer;
    }

    /**
     * @inheritdoc
     */
    public function buildSaleView(Common\SaleInterface $sale, View\SaleView $view, array $options)
    {
        if (!$options['editable'] || !$options['private']) {
            return;
        }

        $buttons = [];

        // Refresh button
        $refreshPath = $this->generateUrl('ekyna_commerce_order_admin_refresh', [
            'orderId' => $sale->getId(),
        ]);
        $buttons[] = new View\Button(
            $refreshPath,
            $this->trans('ekyna_commerce.sale.button.refresh'),
            'fa fa-refresh', [
                'id'            => 'order_refresh',
                'title'         => $this->trans('ekyna_commerce.sale.button.refresh'),
                'class'         => 'btn btn-sm btn-default',
                'data-sale-xhr' => 'get',
            ]
        );

        // Add item button
        $addItemPath = $this->generateUrl('ekyna_commerce_order_item_admin_add', [
            'orderId' => $sale->getId(),
        ]);
        $buttons[] = new View\Button(
            $addItemPath,
            $this->trans('ekyna_commerce.sale.button.item.add'),
            'fa fa-plus', [
                'id'              => 'order_item_add',
                'title'           => 'ekyna_commerce.sale.button.item.add',
                'class'           => 'btn btn-sm btn-primary',
                'data-sale-modal' => null,
            ]
        );

        // New item button
        $newItemPath = $this->generateUrl('ekyna_commerce_order_item_admin_new', [
            'orderId' => $sale->getId(),
        ]);
        $buttons[] = new View\Button(
            $newItemPath,
            $this->trans('ekyna_commerce.sale.button.item.new'),
            'fa fa-plus', [
                'id'              => 'order_item_new',
                'title'           => $this->trans('ekyna_commerce.sale.button.item.new'),
                'class'           => 'btn btn-sm btn-default',
                'data-sale-modal' => null,
            ]
        );

        // New adjustment button
        $newAdjustmentPath = $this->generateUrl('ekyna_commerce_order_adjustment_admin_new', [
            'orderId' => $sale->getId(),
        ]);
        $buttons[] = new View\Button(
            $newAdjustmentPath,
            $this->trans('ekyna_commerce.sale.button.adjustment.new'),
            'fa fa-plus', [
                'id'              => 'order_adjustment_new',
                'title'           => $this->trans('ekyna_commerce.sale.button.adjustment.new'),
                'class'           => 'btn btn-sm btn-default',
                'data-sale-modal' => null,
            ]
        );

        $view->vars['buttons'] = $buttons;
    }

    /**
     * @inheritdoc
     */
    public function buildItemView(Common\SaleItemInterface $item, View\LineView $view, array $options)
    {
        if (!$options['editable'] || !$options['private']) {
            return;
        }

        $actions = [];

        // New adjustment button
        if (!$item->hasAdjustments(Common\AdjustmentTypes::TYPE_DISCOUNT)) {
            $newAdjustmentPath = $this->generateUrl('ekyna_commerce_order_item_adjustment_admin_new', [
                'orderId'     => $item->getSale()->getId(),
                'orderItemId' => $item->getId(),
            ]);
            $actions[] = new View\Action($newAdjustmentPath, 'fa fa-percent', [
                'title'           => $this->trans('ekyna_commerce.sale.button.adjustment.new'),
                'data-sale-modal' => null,
            ]);
        }

        // Information
        if (!$item instanceof Order\OrderItemInterface) {
            throw new \Exception("Unexpected sale item type.");
        }

        $stockUnits = [];
        foreach ($item->getStockAssignments() as $assignment) {
            $stockUnits[] = $assignment->getStockUnit();
        }

        if (!empty($stockUnits)) {
            $view->vars['information'] = $this->stockRenderer->renderStockUnits($stockUnits, [
                'template' => 'EkynaCommerceBundle:Common:sale_stock_unit_list.html.twig',
                'prefix'   => $view->getId() . '_su',
                'class'    => 'table-alt',
            ]);

            $actions[] = new View\Action('javascript: void(0)', 'fa fa-info-circle', [
                'title'               => $this->trans('ekyna_commerce.sale.button.item.information'),
                'data-toggle-details' => $view->getId() . '_information',
            ]);
        }

        if (!$item->isImmutable() && !$item->getParent()) {
            // Configure action
            if ($item->isConfigurable()) {
                $configurePath = $this->generateUrl('ekyna_commerce_order_item_admin_configure', [
                    'orderId'     => $item->getSale()->getId(),
                    'orderItemId' => $item->getId(),
                ]);
                $actions[] = new View\Action($configurePath, 'fa fa-cog', [
                    'title'           => $this->trans('ekyna_commerce.sale.button.item.configure'),
                    'data-sale-modal' => null,
                ]);
            }

            // Edit action
            if (!$item->hasSubjectIdentity()) {
                $editPath = $this->generateUrl('ekyna_commerce_order_item_admin_edit', [
                    'orderId'     => $item->getSale()->getId(),
                    'orderItemId' => $item->getId(),
                ]);
                $actions[] = new View\Action($editPath, 'fa fa-pencil', [
                    'title'           => $this->trans('ekyna_commerce.sale.button.item.edit'),
                    'data-sale-modal' => null,
                ]);
            }

            // Remove action
            $removePath = $this->generateUrl('ekyna_commerce_order_item_admin_remove', [
                'orderId'     => $item->getSale()->getId(),
                'orderItemId' => $item->getId(),
            ]);
            $actions[] = new View\Action($removePath, 'fa fa-remove', [
                'title'         => $this->trans('ekyna_commerce.sale.button.item.remove'),
                'confirm'       => $this->trans('ekyna_commerce.sale.confirm.item.remove'),
                'data-sale-xhr' => null,
            ]);
        }

        $view->vars['actions'] = $actions;
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
        } elseif ($adjustable instanceof Order\OrderItemInterface) {
            $routePrefix = 'ekyna_commerce_order_item_adjustment_admin_';
            $parameters = [
                'orderId'               => $adjustable->getSale()->getId(),
                'orderItemId'           => $adjustable->getId(),
                'orderItemAdjustmentId' => $adjustment->getId(),
            ];
        } else {
            throw new InvalidArgumentException("Unexpected adjustable.");
        }

        $actions = [];

        $editPath = $this->generateUrl($routePrefix . 'edit', $parameters);
        $actions[] = new View\Action($editPath, 'fa fa-pencil', [
            'title'           => $this->trans('ekyna_commerce.sale.button.adjustment.edit'),
            'data-sale-modal' => null,
        ]);

        $removePath = $this->generateUrl($routePrefix . 'remove', $parameters);
        $actions[] = new View\Action($removePath, 'fa fa-remove', [
            'title'         => $this->trans('ekyna_commerce.sale.button.adjustment.remove'),
            'confirm'       => $this->trans('ekyna_commerce.sale.confirm.adjustment.remove'),
            'data-sale-xhr' => null,
        ]);

        $view->vars['actions'] = $actions;
    }

    /**
     * @inheritdoc
     */
    public function buildShipmentView(Common\SaleInterface $sale, View\LineView $view, array $options)
    {
        if (!$options['editable'] || !$options['private']) {
            return;
        }

        $actions = [];

        $editPath = $this->generateUrl('ekyna_commerce_order_admin_edit_shipment', [
            'orderId' => $sale->getId(),
        ]);
        $actions[] = new View\Action($editPath, 'fa fa-pencil', [
            'title'           => $this->trans('ekyna_commerce.sale.button.shipment.edit'),
            'data-sale-modal' => null,
        ]);

        $view->vars['actions'] = $actions;
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
