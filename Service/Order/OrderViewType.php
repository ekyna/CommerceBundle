<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Order;

use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\View;
use Ekyna\Component\Commerce\Order\Model as Order;

/**
 * Class OrderViewType
 * @package Ekyna\Bundle\CommerceBundle\Service\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderViewType extends AbstractViewType
{
    /**
     * @inheritdoc
     */
    public function buildSaleView(Common\SaleInterface $sale, View\AbstractView $view, array $options)
    {
        if ((!$options['editable']) || (!$options['private'])) {
            return;
        }

        $buttons = [];

        // Refresh button
        $refreshPath = $this->generateUrl('ekyna_commerce_order_admin_refresh', [
            'orderId' => $sale->getId(),
        ]);
        $buttons[] = new View\Button($refreshPath, 'ekyna_commerce.sale.button.refresh', 'fa fa-refresh', [
            'title'         => 'ekyna_commerce.sale.button.refresh',
            'class'         => 'btn btn-sm btn-default',
            'data-sale-xhr' => 'get',
        ]);

        // Add item button
        $addItemPath = $this->generateUrl('ekyna_commerce_order_item_admin_add', [
            'orderId' => $sale->getId(),
        ]);
        $buttons[] = new View\Button($addItemPath, 'ekyna_commerce.sale.button.item.add', 'fa fa-plus', [
            'title'           => 'ekyna_commerce.sale.button.item.add',
            'class'           => 'btn btn-sm btn-primary',
            'data-sale-modal' => null,
        ]);

        // New item button
        $newItemPath = $this->generateUrl('ekyna_commerce_order_item_admin_new', [
            'orderId' => $sale->getId(),
        ]);
        $buttons[] = new View\Button($newItemPath, 'ekyna_commerce.sale.button.item.new', 'fa fa-plus', [
            'title'           => 'ekyna_commerce.sale.button.item.new',
            'class'           => 'btn btn-sm btn-default',
            'data-sale-modal' => null,
        ]);

        // New adjustment button
        $newAdjustmentPath = $this->generateUrl('ekyna_commerce_order_adjustment_admin_new', [
            'orderId' => $sale->getId(),
        ]);
        $buttons[] = new View\Button($newAdjustmentPath, 'ekyna_commerce.sale.button.adjustment.new', 'fa fa-plus', [
            'title'           => 'ekyna_commerce.sale.button.adjustment.new',
            'class'           => 'btn btn-sm btn-default',
            'data-sale-modal' => null,
        ]);

        $view->vars['buttons'] = $buttons;
    }

    /**
     * @inheritdoc
     */
    public function buildItemView(Common\SaleItemInterface $item, View\AbstractView $view, array $options)
    {
        if ($item->isImmutable() || (!$options['editable']) || (!$options['private'])) {
            return;
        }

        $actions = [];

        // Configure action
        if ($item->isConfigurable()) {
            $configurePath = $this->generateUrl('ekyna_commerce_order_item_admin_configure', [
                'orderId'     => $item->getSale()->getId(),
                'orderItemId' => $item->getId(),
            ]);
            $actions[] = new View\Action($configurePath, 'fa fa-cog', [
                'title'           => 'ekyna_commerce.sale.button.item.configure',
                'data-sale-modal' => null,
            ]);
        }

        // Edit action
        $editPath = $this->generateUrl('ekyna_commerce_order_item_admin_edit', [
            'orderId'     => $item->getSale()->getId(),
            'orderItemId' => $item->getId(),
        ]);
        $actions[] = new View\Action($editPath, 'fa fa-pencil', [
            'title'           => 'ekyna_commerce.sale.button.item.edit',
            'data-sale-modal' => null,
        ]);

        // Remove action
        $removePath = $this->generateUrl('ekyna_commerce_order_item_admin_remove', [
            'orderId'     => $item->getSale()->getId(),
            'orderItemId' => $item->getId(),
        ]);
        $actions[] = new View\Action($removePath, 'fa fa-remove', [
            'title'         => 'ekyna_commerce.sale.button.item.remove',
            'confirm'       => 'ekyna_commerce.sale.confirm.item.remove',
            'data-sale-xhr' => null,
        ]);

        $view->vars['actions'] = $actions;
    }

    /**
     * @inheritdoc
     */
    public function buildAdjustmentView(Common\AdjustmentInterface $adjustment, View\AbstractView $view, array $options)
    {
        if ($adjustment->isImmutable() || (!$options['editable']) || (!$options['private'])) {
            return;
        }

        // Only for sale adjustments
        $adjustable = $adjustment->getAdjustable();
        if (!$adjustable instanceof Order\OrderAdjustmentInterface) {
            return;
        }

        $actions = [];

        $editPath = $this->generateUrl('ekyna_commerce_order_adjustment_admin_edit', [
            'orderId'           => $adjustable->getId(),
            'orderAdjustmentId' => $adjustment->getId(),
        ]);
        $actions[] = new View\Action($editPath, 'fa fa-pencil', [
            'title'           => 'ekyna_commerce.sale.button.adjustment.edit',
            'data-sale-modal' => null,
        ]);

        $removePath = $this->generateUrl('ekyna_commerce_order_adjustment_admin_remove', [
            'orderId'           => $adjustable->getId(),
            'orderAdjustmentId' => $adjustment->getId(),
        ]);
        $actions[] = new View\Action($removePath, 'fa fa-remove', [
            'title'         => 'ekyna_commerce.sale.button.adjustment.remove',
            'confirm'       => 'ekyna_commerce.sale.confirm.adjustment.remove',
            'data-sale-xhr' => null,
        ]);

        $view->vars['actions'] = $actions;
    }

    /**
     * @inheritdoc
     */
    public function buildShipmentView(Common\SaleInterface $sale, View\AbstractView $view, array $options)
    {
        $actions = [];

        $editPath = $this->generateUrl('ekyna_commerce_order_admin_edit_shipment', [
            'orderId' => $sale->getId(),
        ]);
        $actions[] = new View\Action($editPath, 'fa fa-pencil', [
            'title'           => 'ekyna_commerce.sale.button.shipment.edit',
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
