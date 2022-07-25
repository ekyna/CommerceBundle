<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Cart;

use Ekyna\Bundle\CommerceBundle\Action\Admin;
use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Component\Commerce\Cart\Model as Cart;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\View;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;

/**
 * Class CartViewType
 * @package Ekyna\Bundle\CommerceBundle\Service\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartViewType extends AbstractViewType
{
    private ShipmentPriceResolverInterface $shipmentPriceResolver;

    public function setShipmentPriceResolver(ShipmentPriceResolverInterface $resolver): void
    {
        $this->shipmentPriceResolver = $resolver;
    }

    public function buildSaleView(Common\SaleInterface $sale, View\SaleView $view, array $options): void
    {
        if (!$options['editable'] || !$options['private']) {
            return;
        }

        // Refresh button
        $refreshPath = $this->resourceUrl($sale, Admin\Sale\RefreshAction::class);
        $view->addButton(new View\Button(
            $refreshPath,
            $this->trans('button.refresh', [], 'EkynaUi'),
            'fa fa-refresh',
            [
                'title'         => $this->trans('button.refresh', [], 'EkynaUi'),
                'class'         => 'btn btn-sm btn-default',
                'data-sale-xhr' => 'get',
            ]
        ));

        // Add item button
        $addItemPath = $this->resourceUrl('ekyna_commerce.cart_item', Admin\Sale\Item\AddAction::class, [
            'cartId' => $sale->getId(),
        ]);
        $view->addButton(new View\Button(
            $addItemPath,
            $this->trans('sale.button.item.add', [], 'EkynaCommerce'),
            'fa fa-plus',
            [
                'title'           => $this->trans('sale.button.item.add', [], 'EkynaCommerce'),
                'class'           => 'btn btn-sm btn-primary',
                'data-sale-modal' => null,
            ]
        ));

        // New item button
        $newItemPath = $this->resourceUrl('ekyna_commerce.cart_item', Admin\Sale\Item\CreateAction::class, [
            'cartId' => $sale->getId(),
        ]);
        $view->addButton(new View\Button(
            $newItemPath,
            $this->trans('sale.button.item.new', [], 'EkynaCommerce'),
            'fa fa-plus',
            [
                'title'           => $this->trans('sale.button.item.new', [], 'EkynaCommerce'),
                'class'           => 'btn btn-sm btn-default',
                'data-sale-modal' => null,
            ]
        ));
    }

    public function buildItemView(Common\SaleItemInterface $item, View\LineView $view, array $options): void
    {
        if ($item->isImmutable() || $item->getParent() || !$options['editable']) {
            return;
        }

        // Configure action
        if ($item->isConfigurable()) {
            if ($options['private']) {
                $configurePath = $this->resourceUrl($item, Admin\Sale\Item\ConfigureAction::class);
            } else {
                $configurePath = $this->generateUrl('ekyna_commerce_cart_item_configure', [
                    'itemId' => $item->getId(),
                ]);
            }
            $view->addAction(new View\Action($configurePath, 'fa fa-cog', [
                'title'           => $this->trans('sale.button.item.configure', [], 'EkynaCommerce'),
                'data-sale-modal' => null,
                'class'           => 'text-primary',
            ]));
        }

        if ($options['private']) {
            // Sync with subject
            if ($item->getSubjectIdentity()->hasIdentity()) {
                $syncPath = $this->resourceUrl($item, Admin\Sale\Item\SyncSubjectAction::class);
                $view->addAction(new View\Action($syncPath, 'fa fa-cube', [
                    'title'         => $this->trans('sale.button.item.sync_subject', [], 'EkynaCommerce'),
                    'confirm'       => $this->trans('sale.confirm.item.sync_subject', [], 'EkynaCommerce'),
                    'data-sale-xhr' => null,
                    'class'         => 'text-warning',
                ]));
            }

            // Edit action
            $editPath = $this->resourceUrl($item, Admin\Sale\Item\UpdateAction::class);
            $view->addAction(new View\Action($editPath, 'fa fa-pencil', [
                'title'           => $this->trans('sale.button.item.edit', [], 'EkynaCommerce'),
                'data-sale-modal' => null,
                'class'           => 'text-warning',
            ]));

            // Move up
            if (0 < $item->getPosition()) {
                $moveUpPath = $this->resourceUrl($item, Admin\Sale\Item\MoveUpAction::class);
                $view->addAction(new View\Action($moveUpPath, 'fa fa-arrow-up', [
                    'title'         => $this->trans('button.move_up', [], 'EkynaUi'),
                    'data-sale-xhr' => 'get',
                    'class'         => 'text-muted',
                ]));
            }

            // Move down
            if (!$item->isLast()) {
                $moveDownPath = $this->resourceUrl($item, Admin\Sale\Item\MoveDownAction::class);
                $view->addAction(new View\Action($moveDownPath, 'fa fa-arrow-down', [
                    'title'         => $this->trans('button.move_down', [], 'EkynaUi'),
                    'data-sale-xhr' => 'get',
                    'class'         => 'text-muted',
                ]));
            }
        }

        // Remove action
        $removeOptions = [
            'title'         => $this->trans('sale.button.item.remove', [], 'EkynaCommerce'),
            'class'         => 'text-danger',
        ];
        if ($options['private']) {
            $removePath = $this->resourceUrl($item, Admin\Sale\Item\DeleteAction::class);
            $removeOptions['data-sale-modal'] = null;
        } else {
            $removePath = $this->generateUrl('ekyna_commerce_cart_item_remove', [
                'itemId' => $item->getId(),
            ]);
            $removeOptions = array_replace($removeOptions, [
                'confirm'       => $this->trans('sale.confirm.item.remove', [], 'EkynaCommerce'),
                'data-sale-xhr' => null,
            ]);
        }
        $view->addAction(new View\Action($removePath, 'fa fa-remove', $removeOptions));
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
        $view->addAction(new View\Action($editPath, 'fa fa-pencil', [
            'title'           => $this->trans('sale.button.shipment.edit', [], 'EkynaCommerce'),
            'data-sale-modal' => null,
            'class'           => 'text-warning',
        ]));
    }

    /**
     * Sets the shipment line view's class.
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
        return $sale instanceof Cart\CartInterface;
    }

    public function getName(): string
    {
        return 'ekyna_commerce_cart';
    }
}
