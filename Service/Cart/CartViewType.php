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
    public function buildItemView(Common\SaleItemInterface $item, View\LineView $view, array $options)
    {
        if ($item->isImmutable() || $item->getParent() || !$options['editable']) {
            return;
        }

        // Configure action
        if ($item->isConfigurable()) {
            $configurePath = $this->generateUrl('ekyna_commerce_cart_item_configure', [
                'itemId' => $item->getId(),
            ]);
            $view->addAction(new View\Action($configurePath, 'fa fa-cog', [
                'title'           => $this->trans('ekyna_commerce.sale.button.item.configure'),
                'data-sale-modal' => null,
                'class'           => 'text-primary',
            ]));
        }

        // Remove action
        $removePath = $this->generateUrl('ekyna_commerce_cart_item_remove', [
            'itemId' => $item->getId(),
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
        if ($adjustment->isImmutable() || !$options['editable']) {
            return;
        }

        $adjustable = $adjustment->getAdjustable();
        if ($adjustable instanceof Cart\CartAdjustmentInterface) {
            $removePath = $this->generateUrl('ekyna_commerce_cart_adjustment_remove', [
                'adjustmentId' => $adjustment->getId(),
            ]);
        } elseif ($adjustable instanceof Cart\CartItemAdjustmentInterface) {
            $removePath = $this->generateUrl('ekyna_commerce_cart_item_adjustment_remove', [
                'itemId'       => $adjustable->getId(),
                'adjustmentId' => $adjustment->getId(),
            ]);
        } else {
            throw new InvalidArgumentException('Unexpected adjustable.');
        }

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
