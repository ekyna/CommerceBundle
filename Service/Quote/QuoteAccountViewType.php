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
     * @inheritdoc
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
            $this->trans('ekyna_core.button.refresh'),
            'fa fa-refresh',
            [
                'title'         => $this->trans('ekyna_core.button.refresh'),
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
            $this->trans('ekyna_commerce.sale.button.item.add'),
            'fa fa-plus',
            [
                'title'           => $this->trans('ekyna_commerce.sale.button.item.add'),
                'class'           => 'btn btn-sm btn-primary',
                'data-sale-modal' => null,
            ]
        ));
    }

    /**
     * @inheritdoc
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
                'number' => $item->getSale()->getNumber(),
                'id'     => $item->getId(),
            ]);
            $view->addAction(new View\Action($moveUpPath, 'fa fa-arrow-up', [
                'title'         => $this->trans('ekyna_core.button.move_up'),
                'data-sale-xhr' => 'get',
                'class'         => 'text-muted',
            ]));
        }

        // Move down
        if (!$item->isLast()) {
            $moveUpPath = $this->generateUrl('ekyna_commerce_account_quote_item_move_down', [
                'number' => $item->getSale()->getNumber(),
                'id'     => $item->getId(),
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

        // Remove action
        $removePath = $this->generateUrl('ekyna_commerce_account_quote_item_remove', [
            'number' => $item->getSale()->getNumber(),
            'id'     => $item->getId(),
        ]);
        $view->addAction(new View\Action($removePath, 'fa fa-remove', [
            'title'         => $this->trans('ekyna_commerce.sale.button.item.remove'),
            'confirm'       => $this->trans('ekyna_commerce.sale.confirm.item.remove'),
            'data-sale-xhr' => null,
            'class'         => 'text-danger',
        ]));

        // Configure action
        if ($item->isConfigurable()) {
            $configurePath = $this->generateUrl('ekyna_commerce_account_quote_item_configure', [
                'number' => $item->getSale()->getNumber(),
                'id'     => $item->getId(),
            ]);
            $view->addAction(new View\Action($configurePath, 'fa fa-cog', [
                'title'           => $this->trans('ekyna_commerce.sale.button.item.configure'),
                'data-sale-modal' => null,
                'class'           => 'text-primary',
            ]));
        }
    }

    /**
     * @inheritdoc
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
