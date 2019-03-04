<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Cart;

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
    /**
     * @var ShipmentPriceResolverInterface
     */
    private $shipmentPriceResolver;


    /**
     * Sets the shipment price resolver.
     *
     * @param ShipmentPriceResolverInterface $resolver
     */
    public function setShipmentPriceResolver(ShipmentPriceResolverInterface $resolver)
    {
        $this->shipmentPriceResolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function buildSaleView(Common\SaleInterface $sale, View\SaleView $view, array $options)
    {
        if (!$options['editable'] || !$options['private']) {
            return;
        }

        // Refresh button
        $refreshPath = $this->generateUrl('ekyna_commerce_cart_admin_refresh', [
            'cartId' => $sale->getId(),
        ]);
        $view->addButton(new View\Button(
            $refreshPath,
            $this->trans('ekyna_core.button.refresh'),
            'fa fa-refresh',
            [
                'title'         => $this->trans('ekyna_core.button.refresh'),
                'class'         => 'btn btn-sm btn-default',
                'data-sale-xhr' => 'get',
            ]
        ));

        // Add item button
        $addItemPath = $this->generateUrl('ekyna_commerce_cart_item_admin_add', [
            'cartId' => $sale->getId(),
        ]);
        $view->addButton(new View\Button(
            $addItemPath,
            $this->trans('ekyna_commerce.sale.button.item.add'),
            'fa fa-plus',
            [
                'title'           => $this->trans('ekyna_commerce.sale.button.item.add'),
                'class'           => 'btn btn-sm btn-primary',
                'data-sale-modal' => null,
            ]
        ));

        // New item button
        $newItemPath = $this->generateUrl('ekyna_commerce_cart_item_admin_new', [
            'cartId' => $sale->getId(),
        ]);
        $view->addButton(new View\Button(
            $newItemPath,
            $this->trans('ekyna_commerce.sale.button.item.new'),
            'fa fa-plus',
            [
                'title'           => $this->trans('ekyna_commerce.sale.button.item.new'),
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
        if ($item->isImmutable() || $item->getParent() || !$options['editable']) {
            return;
        }

        // Configure action
        if ($item->isConfigurable()) {
            if ($options['private']) {
                $configurePath = $this->generateUrl('ekyna_commerce_cart_item_admin_configure', [
                    'cartId'     => $item->getSale()->getId(),
                    'cartItemId' => $item->getId(),
                ]);
            } else {
                $configurePath = $this->generateUrl('ekyna_commerce_cart_item_configure', [
                    'itemId' => $item->getId(),
                ]);
            }
            $view->addAction(new View\Action($configurePath, 'fa fa-cog', [
                'title'           => $this->trans('ekyna_commerce.sale.button.item.configure'),
                'data-sale-modal' => null,
                'class'           => 'text-primary',
            ]));
        }

        if ($options['private']) {
            // Edit action
            $editPath = $this->generateUrl('ekyna_commerce_cart_item_admin_edit', [
                'cartId'     => $item->getSale()->getId(),
                'cartItemId' => $item->getId(),
            ]);
            $view->addAction(new View\Action($editPath, 'fa fa-pencil', [
                'title'           => $this->trans('ekyna_commerce.sale.button.item.edit'),
                'data-sale-modal' => null,
                'class'           => 'text-warning',
            ]));

            // Move up
            if (0 < $item->getPosition()) {
                $moveUpPath = $this->generateUrl('ekyna_commerce_cart_item_admin_move_up', [
                    'cartId'     => $item->getSale()->getId(),
                    'cartItemId' => $item->getId(),
                ]);
                $view->addAction(new View\Action($moveUpPath, 'fa fa-arrow-up', [
                    'title'         => $this->trans('ekyna_core.button.move_up'),
                    'data-sale-xhr' => 'get',
                    'class'         => 'text-muted',
                ]));
            }

            // Move down
            if (!$item->isLast()) {
                $moveUpPath = $this->generateUrl('ekyna_commerce_cart_item_admin_move_down', [
                    'cartId'     => $item->getSale()->getId(),
                    'cartItemId' => $item->getId(),
                ]);
                $view->addAction(new View\Action($moveUpPath, 'fa fa-arrow-down', [
                    'title'         => $this->trans('ekyna_core.button.move_down'),
                    'data-sale-xhr' => 'get',
                    'class'         => 'text-muted',
                ]));
            }
        }

        // Remove action
        if ($options['private']) {
            $removePath = $this->generateUrl('ekyna_commerce_cart_item_admin_remove', [
                'cartId'     => $item->getSale()->getId(),
                'cartItemId' => $item->getId(),
            ]);
        } else {
            $removePath = $this->generateUrl('ekyna_commerce_cart_item_remove', [
                'itemId' => $item->getId(),
            ]);
        }
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
    public function buildShipmentView(Common\SaleInterface $sale, View\LineView $view, array $options)
    {
        if (!$options['editable'] || !$options['private']) {
            return;
        }

        $this->setShipmentViewClass($sale, $view);

        if ($sale->isAutoShipping()) {
            return;
        }

        $editPath = $this->generateUrl('ekyna_commerce_quote_admin_edit_shipment', [
            'quoteId' => $sale->getId(),
        ]);
        $view->addAction(new View\Action($editPath, 'fa fa-pencil', [
            'title'           => $this->trans('ekyna_commerce.sale.button.shipment.edit'),
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
    private function setShipmentViewClass(Common\SaleInterface $sale, View\LineView $view)
    {
        if (null === $p = $this->shipmentPriceResolver->getPriceBySale($sale)) {
            $view->addClass('danger');

            return;
        }

        if (0 !== bccomp($p->getPrice(), $sale->getShipmentAmount(), 3)) {
            $view->addClass('warning');
        }
    }

    /**
     * @inheritdoc
     */
    public function supportsSale(Common\SaleInterface $sale)
    {
        return $sale instanceof Cart\CartInterface;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'ekyna_commerce_cart';
    }
}
