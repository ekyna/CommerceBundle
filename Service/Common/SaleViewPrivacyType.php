<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\View;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;

/**
 * Class SaleViewPrivacyType
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleViewPrivacyType extends AbstractViewType
{
    /**
     * @inheritDoc
     */
    public function buildItemView(Model\SaleItemInterface $item, View\LineView $view, array $options): void
    {
        if (!$options['private']) {
            return;
        }

        if ($item->isPrivate()) {
            $view->vars['attr']['data-parent'] = $item->getParent()->getId();
        }

        if ($item->hasPrivateChildren()) {
            $view->addAction(new View\Action('javascript: void(0)', 'fa fa-info-circle', [
                'title'                     => $this->trans('sale.button.item.detail', [], 'EkynaCommerce'),
                'data-sale-toggle-children' => $item->getId(),
            ]));
        }
    }

    /**
     * @inheritDoc
     */
    public function supportsSale(Model\SaleInterface $sale): bool
    {
        return $sale instanceof OrderInterface
            || $sale instanceof QuoteInterface;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'ekyna_commerce_sale_privacy';
    }

    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return 1024;
    }
}
