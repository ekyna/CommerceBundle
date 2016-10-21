<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Cart;

use Ekyna\Bundle\CommerceBundle\Service\AbstractViewVarsBuilder;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\View;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class CartViewVarsBuilder
 * @package Ekyna\Bundle\CommerceBundle\Service\Cart
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartViewVarsBuilder extends AbstractViewVarsBuilder
{
    /**
     * @inheritdoc
     */
    public function buildSaleViewVars(Model\SaleInterface $sale, array $options = [])
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function buildItemViewVars(Model\SaleItemInterface $item, array $options = [])
    {
        if ($item->isImmutable() || !$options['editable']) {
            return [];
        }

        $actions = [];

        // Configure action
        if ($item->isConfigurable()) {
            $configurePath = $this->generateUrl('ekyna_commerce_cart_configure_item', [
                'itemId' => $item->getId(),
            ]);
            $actions[] = new View\Action($configurePath, 'fa fa-cog', [
                'title'           => 'ekyna_commerce.sale.button.configure_item',
                'data-sale-modal' => null,
            ]);
        }

        // Remove action
        $removePath = $this->generateUrl('ekyna_commerce_cart_remove_item', [
            'itemId' => $item->getId(),
        ]);
        $actions[] = new View\Action($removePath, 'fa fa-remove', [
            'title'         => 'ekyna_commerce.sale.button.remove_item',
            'confirm'       => 'ekyna_commerce.sale.confirm.remove_item',
            'data-sale-xhr' => null,
        ]);

        return [
            'actions' => $actions,
        ];
    }

    /**
     * @inheritdoc
     */
    public function buildAdjustmentViewVars(Model\AdjustmentInterface $adjustment, array $options = [])
    {
        if ($adjustment->isImmutable() || !$options['editable']) {
            return [];
        }

        $actions = [];

        $adjustable = $adjustment->getAdjustable();
        if ($adjustable instanceof Model\SaleInterface) {
            $removePath = $this->generateUrl('ekyna_commerce_cart_remove_adjustment', [
                'adjustmentId' => $adjustment->getId(),
            ]);
        } elseif ($adjustable instanceof Model\SaleItemInterface) {
            $removePath = $this->generateUrl('ekyna_commerce_cart_remove_item_adjustment', [
                'itemId'       => $adjustable->getId(),
                'adjustmentId' => $adjustment->getId(),
            ]);
        } else {
            throw new InvalidArgumentException('Unexpected adjustable.');
        }

        $actions[] = new View\Action($removePath, 'fa fa-remove', [
            'title'         => 'ekyna_commerce.sale.button.remove_adjustment',
            'confirm'       => 'ekyna_commerce.sale.confirm.remove_adjustment',
            'data-sale-xhr' => null,
        ]);

        return [
            'actions' => $actions,
        ];
    }

    /**
     * @inheritdoc
     */
    public function buildShipmentViewVars(Model\SaleInterface $sale, array $options = [])
    {
        return [];
    }
}
