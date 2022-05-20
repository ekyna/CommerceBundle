<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Quote;

use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\View;
use Ekyna\Component\Commerce\Quote\Model as Quote;

/**
 * Class QuoteAccountViewType
 * @package Ekyna\Bundle\CommerceBundle\Service\Quote
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAccountViewType extends AbstractViewType
{
    /**
     * @inheritDoc
     */
    public function buildSaleView(Common\SaleInterface $sale, View\SaleView $view, array $options): void
    {
        if (!$options['editable'] || $options['private']) {
            return;
        }

        // Refresh button
        $refreshPath = $this->generateUrl('ekyna_commerce_account_quote_refresh', [
            'number' => $sale->getNumber(),
        ]);
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
        $addItemPath = $this->generateUrl('ekyna_commerce_account_quote_item_add', [
            'number' => $sale->getNumber(),
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
    }

    /**
     * @inheritDoc
     */
    public function buildItemView(Common\SaleItemInterface $item, View\LineView $view, array $options): void
    {
        if (!$options['editable'] || $options['private']) {
            return;
        }

        // Abort if has parent
        if ($item->getParent()) {
            return;
        }

        // Move up
        if (0 < $item->getPosition()) {
            $moveUpPath = $this->generateUrl('ekyna_commerce_account_quote_item_move_up', [
                'number' => $item->getRootSale()->getNumber(),
                'id'     => $item->getId(),
            ]);
            $view->addAction(new View\Action($moveUpPath, 'fa fa-arrow-up', [
                'title'         => $this->trans('button.move_up', [], 'EkynaUi'),
                'data-sale-xhr' => 'get',
                'class'         => 'text-muted',
            ]));
        }

        // Move down
        if (!$item->isLast()) {
            $moveUpPath = $this->generateUrl('ekyna_commerce_account_quote_item_move_down', [
                'number' => $item->getRootSale()->getNumber(),
                'id'     => $item->getId(),
            ]);
            $view->addAction(new View\Action($moveUpPath, 'fa fa-arrow-down', [
                'title'         => $this->trans('button.move_down', [], 'EkynaUi'),
                'data-sale-xhr' => 'get',
                'class'         => 'text-muted',
            ]));
        }

        // Abort if immutable
        if ($item->isImmutable()) {
            return;
        }

        // Remove action
        $removePath = $this->generateUrl('ekyna_commerce_account_quote_item_remove', [
            'number' => $item->getRootSale()->getNumber(),
            'id'     => $item->getId(),
        ]);
        $view->addAction(new View\Action($removePath, 'fa fa-remove', [
            'title'         => $this->trans('sale.button.item.remove', [], 'EkynaCommerce'),
            'confirm'       => $this->trans('sale.confirm.item.remove', [], 'EkynaCommerce'),
            'data-sale-xhr' => null,
            'class'         => 'text-danger',
        ]));

        // Configure action
        if ($item->isConfigurable()) {
            $configurePath = $this->generateUrl('ekyna_commerce_account_quote_item_configure', [
                'number' => $item->getRootSale()->getNumber(),
                'id'     => $item->getId(),
            ]);
            $view->addAction(new View\Action($configurePath, 'fa fa-cog', [
                'title'           => $this->trans('sale.button.item.configure', [], 'EkynaCommerce'),
                'data-sale-modal' => null,
                'class'           => 'text-primary',
            ]));
        }
    }

    /**
     * @inheritDoc
     */
    public function supportsSale(Common\SaleInterface $sale): bool
    {
        return $sale instanceof Quote\QuoteInterface && $sale->isEditable();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'ekyna_commerce_quote_account';
    }
}
