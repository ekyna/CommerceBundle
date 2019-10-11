<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Quote;

use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Common\View;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Model as Quote;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;

/**
 * Class QuoteAdminViewType
 * @package Ekyna\Bundle\CommerceBundle\Service\Quote
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAdminViewType extends AbstractViewType
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
        $refreshPath = $this->generateUrl('ekyna_commerce_quote_admin_refresh', [
            'quoteId' => $sale->getId(),
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
        $addItemPath = $this->generateUrl('ekyna_commerce_quote_item_admin_add', [
            'quoteId' => $sale->getId(),
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
        $newItemPath = $this->generateUrl('ekyna_commerce_quote_item_admin_new', [
            'quoteId' => $sale->getId(),
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

        // New adjustment button
        $newAdjustmentPath = $this->generateUrl('ekyna_commerce_quote_adjustment_admin_new', [
            'quoteId' => $sale->getId(),
        ]);
        $view->addButton(new View\Button(
            $newAdjustmentPath,
            $this->trans('ekyna_commerce.sale.button.adjustment.new'),
            'fa fa-plus',
            [
                'title'           => $this->trans('ekyna_commerce.sale.button.adjustment.new'),
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
        if (!$options['editable'] || !$options['private']) {
            return;
        }

        $sale = $item->getSale();

        // Manual adjustments
        if (!$item->getSubjectIdentity()->hasIdentity() || (
                !$sale->isAutoDiscount() && !$sale->isSample() &&
                !($item->isPrivate() || ($item->isCompound() && !$item->hasPrivateChildren()))
            )
        ) {
            $adjustment = current($item->getAdjustments(Common\AdjustmentTypes::TYPE_DISCOUNT)->toArray());
            if (false !== $adjustment) {
                $routePrefix = 'ekyna_commerce_quote_item_adjustment_admin_';
                $parameters = [
                    'quoteId'               => $item->getSale()->getId(),
                    'quoteItemId'           => $item->getId(),
                    'quoteItemAdjustmentId' => $adjustment->getId(),
                ];

                $editPath = $this->generateUrl($routePrefix . 'edit', $parameters);
                $view->addAction(new View\Action($editPath, 'fa fa-percent', [
                    'title'           => $this->trans('ekyna_commerce.sale.button.adjustment.edit'),
                    'class'           => 'text-warning',
                    'data-sale-modal' => null,
                ]));

                $removePath = $this->generateUrl($routePrefix . 'remove', $parameters);
                $view->addAction(new View\Action($removePath, 'fa fa-percent', [
                    'title'         => $this->trans('ekyna_commerce.sale.button.adjustment.remove'),
                    'confirm'       => $this->trans('ekyna_commerce.sale.confirm.adjustment.remove'),
                    'class'         => 'text-danger',
                    'data-sale-xhr' => null,
                ]));
            } else {
                // New adjustment button
                $newAdjustmentPath = $this->generateUrl('ekyna_commerce_quote_item_adjustment_admin_new', [
                    'quoteId'     => $item->getSale()->getId(),
                    'quoteItemId' => $item->getId(),
                ]);
                $view->addAction(new View\Action($newAdjustmentPath, 'fa fa-percent', [
                    'title'           => $this->trans('ekyna_commerce.sale.button.adjustment.new'),
                    'data-sale-modal' => null,
                    'class'           => 'text-success',
                ]));
            }
        }

        // Abort if has parent
        if (!$item->getParent()) {
            // Move up
            if (0 < $item->getPosition()) {
                $moveUpPath = $this->generateUrl('ekyna_commerce_quote_item_admin_move_up', [
                    'quoteId'     => $item->getSale()->getId(),
                    'quoteItemId' => $item->getId(),
                ]);
                $view->addAction(new View\Action($moveUpPath, 'fa fa-arrow-up', [
                    'title'         => $this->trans('ekyna_core.button.move_up'),
                    'data-sale-xhr' => 'get',
                    'class'         => 'text-muted',
                ]));
            }

            // Move down
            if (!$item->isLast()) {
                $moveUpPath = $this->generateUrl('ekyna_commerce_quote_item_admin_move_down', [
                    'quoteId'     => $item->getSale()->getId(),
                    'quoteItemId' => $item->getId(),
                ]);
                $view->addAction(new View\Action($moveUpPath, 'fa fa-arrow-down', [
                    'title'         => $this->trans('ekyna_core.button.move_down'),
                    'data-sale-xhr' => 'get',
                    'class'         => 'text-muted',
                ]));
            }

            // Abort if immutable
            if (!$item->isImmutable()) {
                // Remove action
                $removePath = $this->generateUrl('ekyna_commerce_quote_item_admin_remove', [
                    'quoteId'     => $item->getSale()->getId(),
                    'quoteItemId' => $item->getId(),
                ]);
                $view->addAction(new View\Action($removePath, 'fa fa-remove', [
                    'title'         => $this->trans('ekyna_commerce.sale.button.item.remove'),
                    'confirm'       => $this->trans('ekyna_commerce.sale.confirm.item.remove'),
                    'data-sale-xhr' => null,
                    'class'         => 'text-danger',
                ]));
            }
        }

        // Edit action
        //if (!$item->isCompound()) {
        $editPath = $this->generateUrl('ekyna_commerce_quote_item_admin_edit', [
            'quoteId'     => $item->getSale()->getId(),
            'quoteItemId' => $item->getId(),
        ]);
        $view->addAction(new View\Action($editPath, 'fa fa-pencil', [
            'title'           => $this->trans('ekyna_commerce.sale.button.item.edit'),
            'data-sale-modal' => null,
            'class'           => 'text-warning',
        ]));
        //}

        if (!$item->isImmutable() && !$item->getParent()) {
            // Configure action
            if ($item->isConfigurable()) {
                $configurePath = $this->generateUrl('ekyna_commerce_quote_item_admin_configure', [
                    'quoteId'     => $item->getSale()->getId(),
                    'quoteItemId' => $item->getId(),
                ]);
                $view->addAction(new View\Action($configurePath, 'fa fa-cog', [
                    'title'           => $this->trans('ekyna_commerce.sale.button.item.configure'),
                    'data-sale-modal' => null,
                    'class'           => 'text-primary',
                ]));
            }
        }
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
        } else {
            throw new InvalidArgumentException("Unexpected adjustable.");
        }

        $editPath = $this->generateUrl($routePrefix . 'edit', $parameters);
        $view->addAction(new View\Action($editPath, 'fa fa-pencil', [
            'title'           => $this->trans('ekyna_commerce.sale.button.adjustment.edit'),
            'data-sale-modal' => null,
            'class'           => 'text-warning',
        ]));

        $removePath = $this->generateUrl($routePrefix . 'remove', $parameters);
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
        return $sale instanceof Quote\QuoteInterface;
    }


    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'ekyna_commerce_quote_admin';
    }
}
