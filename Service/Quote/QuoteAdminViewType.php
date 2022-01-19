<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Quote;

use Ekyna\Bundle\CommerceBundle\Action\Admin;
use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\View;
use Ekyna\Component\Commerce\Quote\Model as Quote;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;

/**
 * Class QuoteAdminViewType
 * @package Ekyna\Bundle\CommerceBundle\Service\Quote
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAdminViewType extends AbstractViewType
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
        $addItemPath = $this->resourceUrl('ekyna_commerce.quote_item', Admin\Sale\Item\AddAction::class, [
            'quoteId' => $sale->getId(),
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
        $newItemPath = $this->resourceUrl('ekyna_commerce.quote_item', Admin\Sale\Item\CreateAction::class, [
            'quoteId' => $sale->getId(),
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

        // New adjustment button
        $newAdjustmentPath = $this->resourceUrl('ekyna_commerce.quote_adjustment', Admin\Sale\Adjustment\CreateAction::class, [
            'quoteId' => $sale->getId(),
        ]);
        $view->addButton(new View\Button(
            $newAdjustmentPath,
            $this->trans('sale.button.adjustment.new', [], 'EkynaCommerce'),
            'fa fa-plus',
            [
                'title'           => $this->trans('sale.button.adjustment.new', [], 'EkynaCommerce'),
                'class'           => 'btn btn-sm btn-default',
                'data-sale-modal' => null,
            ]
        ));
    }

    public function buildItemView(Common\SaleItemInterface $item, View\LineView $view, array $options): void
    {
        if (!$options['editable'] || !$options['private']) {
            return;
        }

        $sale = $item->getSale();

        // Manual adjustments
        if (!$item->getSubjectIdentity()->hasIdentity()
            || (
                !$sale->isAutoDiscount() && !$sale->isSample()
                && !($item->isPrivate()
                    || ($item->isCompound()
                        && !$item->hasPrivateChildren()))
            )
        ) {
            $adjustment = current($item->getAdjustments(Common\AdjustmentTypes::TYPE_DISCOUNT)->toArray());
            if (false !== $adjustment) {
                $editPath = $this->resourceUrl($adjustment, Admin\Sale\Adjustment\UpdateAction::class);
                $view->addAction(new View\Action($editPath, 'fa fa-percent', [
                    'title'           => $this->trans('sale.button.adjustment.edit', [], 'EkynaCommerce'),
                    'class'           => 'text-warning',
                    'data-sale-modal' => null,
                ]));

                $removePath = $this->resourceUrl($adjustment, Admin\Sale\Adjustment\DeleteAction::class);
                $view->addAction(new View\Action($removePath, 'fa fa-percent', [
                    'title'         => $this->trans('sale.button.adjustment.remove', [], 'EkynaCommerce'),
                    //'confirm'       => $this->trans('sale.confirm.adjustment.remove', [], 'EkynaCommerce'),
                    //'data-sale-xhr' => null,
                    'data-sale-modal' => null,
                    'class'         => 'text-danger',
                ]));
            } else {
                // New adjustment button
                $newAdjustmentPath = $this->resourceUrl(
                    'ekyna_commerce.quote_item_adjustment',
                    Admin\Sale\Adjustment\CreateAction::class,
                    [
                        'quoteId'     => $item->getSale()->getId(),
                        'quoteItemId' => $item->getId(),
                    ]
                );
                $view->addAction(new View\Action($newAdjustmentPath, 'fa fa-percent', [
                    'title'           => $this->trans('sale.button.adjustment.new', [], 'EkynaCommerce'),
                    'data-sale-modal' => null,
                    'class'           => 'text-success',
                ]));
            }
        }

        // Abort if has parent
        if (!$item->getParent()) {
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

            // Abort if immutable
            if (!$item->isImmutable()) {
                // Remove action
                $removePath = $this->resourceUrl($item, Admin\Sale\Item\DeleteAction::class);
                $view->addAction(new View\Action($removePath, 'fa fa-remove', [
                    'title'         => $this->trans('sale.button.item.remove', [], 'EkynaCommerce'),
                    //'confirm'       => $this->trans('sale.confirm.item.remove', [], 'EkynaCommerce'),
                    //'data-sale-xhr' => null,
                    'data-sale-modal' => null,
                    'class'         => 'text-danger',
                ]));
            }
        }

        // Edit action
        //if (!$item->isCompound()) {
        $editPath = $this->resourceUrl($item, Admin\Sale\Item\UpdateAction::class);
        $view->addAction(new View\Action($editPath, 'fa fa-pencil', [
            'title'           => $this->trans('sale.button.item.edit', [], 'EkynaCommerce'),
            'data-sale-modal' => null,
            'class'           => 'text-warning',
        ]));
        //}

        if (!$item->isImmutable() && !$item->getParent()) {
            // Configure action
            if ($item->isConfigurable()) {
                $configurePath = $this->resourceUrl($item, Admin\Sale\Item\ConfigureAction::class);
                $view->addAction(new View\Action($configurePath, 'fa fa-cog', [
                    'title'           => $this->trans('sale.button.item.configure', [], 'EkynaCommerce'),
                    'data-sale-modal' => null,
                    'class'           => 'text-primary',
                ]));
            }
        }
    }

    public function buildAdjustmentView(
        Common\AdjustmentInterface $adjustment,
        View\LineView $view,
        array $options
    ): void {
        if ($adjustment->isImmutable() || !$options['editable'] || !$options['private']) {
            return;
        }

        $editPath = $this->resourceUrl($adjustment, Admin\Sale\Adjustment\UpdateAction::class);
        $view->addAction(new View\Action($editPath, 'fa fa-pencil', [
            'title'           => $this->trans('sale.button.adjustment.edit', [], 'EkynaCommerce'),
            'data-sale-modal' => null,
            'class'           => 'text-warning',
        ]));

        $removePath = $this->resourceUrl($adjustment, Admin\Sale\Adjustment\DeleteAction::class);
        $view->addAction(new View\Action($removePath, 'fa fa-remove', [
            'title'         => $this->trans('sale.button.adjustment.remove', [], 'EkynaCommerce'),
            //'confirm'       => $this->trans('sale.confirm.adjustment.remove', [], 'EkynaCommerce'),
            //'data-sale-xhr' => null,
            'data-sale-modal' => null,
            'class'         => 'text-danger',
        ]));
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
        return $sale instanceof Quote\QuoteInterface;
    }

    public function getName(): string
    {
        return 'ekyna_commerce_quote_admin';
    }
}
