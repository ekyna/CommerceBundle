<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Quote;

use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\View;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Model as Quote;

/**
 * Class QuoteViewType
 * @package Ekyna\Bundle\CommerceBundle\Service\Quote
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteViewType extends AbstractViewType
{
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
        $refreshPath = $this->generateUrl('ekyna_commerce_quote_admin_refresh', [
            'quoteId' => $sale->getId(),
        ]);
        $buttons[] = new View\Button(
            $refreshPath,
            $this->trans('ekyna_commerce.sale.button.refresh'),
            'fa fa-refresh',
            [
                'title'         => $this->trans('ekyna_commerce.sale.button.refresh'),
                'class'         => 'btn btn-sm btn-default',
                'data-sale-xhr' => 'get',
            ]
        );

        // Add item button
        $addItemPath = $this->generateUrl('ekyna_commerce_quote_item_admin_add', [
            'quoteId' => $sale->getId(),
        ]);
        $buttons[] = new View\Button(
            $addItemPath,
            $this->trans('ekyna_commerce.sale.button.item.add'),
            'fa fa-plus',
            [
                'title'           => $this->trans('ekyna_commerce.sale.button.item.add'),
                'class'           => 'btn btn-sm btn-primary',
                'data-sale-modal' => null,
            ]
        );

        // New item button
        $newItemPath = $this->generateUrl('ekyna_commerce_quote_item_admin_new', [
            'quoteId' => $sale->getId(),
        ]);
        $buttons[] = new View\Button(
            $newItemPath,
            $this->trans('ekyna_commerce.sale.button.item.new'),
            'fa fa-plus',
            [
                'title'           => $this->trans('ekyna_commerce.sale.button.item.new'),
                'class'           => 'btn btn-sm btn-default',
                'data-sale-modal' => null,
            ]
        );

        // New adjustment button
        $newAdjustmentPath = $this->generateUrl('ekyna_commerce_quote_adjustment_admin_new', [
            'quoteId' => $sale->getId(),
        ]);
        $buttons[] = new View\Button(
            $newAdjustmentPath,
            $this->trans('ekyna_commerce.sale.button.adjustment.new'),
            'fa fa-plus',
            [
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
            $newAdjustmentPath = $this->generateUrl('ekyna_commerce_quote_item_adjustment_admin_new', [
                'quoteId'     => $item->getSale()->getId(),
                'quoteItemId' => $item->getId(),
            ]);
            $actions[] = new View\Action($newAdjustmentPath, 'fa fa-percent', [
                'title'           => $this->trans('ekyna_commerce.sale.button.adjustment.new'),
                'data-sale-modal' => null,
            ]);
        }

        if (!$item->isImmutable() && !$item->getParent()) {
            // Configure action
            if ($item->isConfigurable()) {
                $configurePath = $this->generateUrl('ekyna_commerce_quote_item_admin_configure', [
                    'quoteId'     => $item->getSale()->getId(),
                    'quoteItemId' => $item->getId(),
                ]);
                $actions[] = new View\Action($configurePath, 'fa fa-cog', [
                    'title'           => $this->trans('ekyna_commerce.sale.button.item.configure'),
                    'data-sale-modal' => null,
                ]);
            }

            // Edit action
            if (!$item->hasSubjectIdentity()) {
                $editPath = $this->generateUrl('ekyna_commerce_quote_item_admin_edit', [
                    'quoteId'     => $item->getSale()->getId(),
                    'quoteItemId' => $item->getId(),
                ]);
                $actions[] = new View\Action($editPath, 'fa fa-pencil', [
                    'title'           => $this->trans('ekyna_commerce.sale.button.item.edit'),
                    'data-sale-modal' => null,
                ]);
            }

            // Remove action
            $removePath = $this->generateUrl('ekyna_commerce_quote_item_admin_remove', [
                'quoteId'     => $item->getSale()->getId(),
                'quoteItemId' => $item->getId(),
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
        if ($adjustment->isImmutable() || !$options['editable'] || !$options['private']) {
            return;
        }

        $adjustable = $adjustment->getAdjustable();
        if ($adjustable instanceof Quote\QuoteInterface) {
            $routePrefix = 'ekyna_commerce_quote_adjustment_admin_';
            $parameters = [
                'quoteId'           => $adjustable->getId(),
                'quoteAdjustmentId' => $adjustment->getId(),
            ];
        } elseif ($adjustable instanceof Quote\QuoteItemInterface) {
            $routePrefix = 'ekyna_commerce_quote_item_adjustment_admin_';
            $parameters = [
                'quoteId'               => $adjustable->getSale()->getId(),
                'quoteItemId'           => $adjustable->getId(),
                'quoteItemAdjustmentId' => $adjustment->getId(),
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

        $editPath = $this->generateUrl('ekyna_commerce_quote_admin_edit_shipment', [
            'quoteId' => $sale->getId(),
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
        return $sale instanceof Quote\QuoteInterface;
    }


    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'ekyna_commerce_quote';
    }
}
