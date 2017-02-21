<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Cart;

use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Component\Commerce\Cart\Model as Cart;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\View;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class CartViewType
 * @package Ekyna\Bundle\CommerceBundle\Service\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartViewType extends AbstractViewType
{
    /**
     * @inheritdoc
     */
    public function buildSaleView(Common\SaleInterface $sale, View\AbstractView $view, array $options)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function buildItemView(Common\SaleItemInterface $item, View\AbstractView $view, array $options)
    {
        if ($item->isImmutable() || !$options['editable']) {
            return;
        }

        $actions = [];

        // Configure action
        if ($item->isConfigurable()) {
            $configurePath = $this->generateUrl('ekyna_commerce_cart_configure_item', [
                'itemId' => $item->getId(),
            ]);
            $actions[] = new View\Action($configurePath, 'fa fa-cog', [
                'title'           => 'ekyna_commerce.sale.button.item.configure',
                'data-sale-modal' => null,
            ]);
        }

        // Remove action
        $removePath = $this->generateUrl('ekyna_commerce_cart_remove_item', [
            'itemId' => $item->getId(),
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
        if ($adjustment->isImmutable() || !$options['editable']) {
            return;
        }

        $actions = [];

        $adjustable = $adjustment->getAdjustable();
        if ($adjustable instanceof Cart\CartAdjustmentInterface) {
            $removePath = $this->generateUrl('ekyna_commerce_cart_remove_adjustment', [
                'adjustmentId' => $adjustment->getId(),
            ]);
        } elseif ($adjustable instanceof Cart\CartItemAdjustmentInterface) {
            $removePath = $this->generateUrl('ekyna_commerce_cart_remove_item_adjustment', [
                'itemId'       => $adjustable->getId(),
                'adjustmentId' => $adjustment->getId(),
            ]);
        } else {
            throw new InvalidArgumentException('Unexpected adjustable.');
        }

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
