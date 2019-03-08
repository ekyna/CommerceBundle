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
    public function buildItemView(Model\SaleItemInterface $item, View\LineView $view, array $options)
    {
        if (!$options['private']) {
            return;
        }

        if ($item->isPrivate()) {
            $view->vars['attr']['data-parent'] = $item->getParent()->getId();
        }

        if ($item->hasPrivateChildren()) {
            $view->addAction(new View\Action('javascript: void(0)', 'fa fa-info-circle', [
                'title'                     => $this->trans('ekyna_commerce.sale.button.item.detail'),
                'data-sale-toggle-children' => $item->getId(),
            ]));
        }
    }

    /**
     * @inheritdoc
     */
    public function supportsSale(Model\SaleInterface $sale)
    {
        return $sale instanceof OrderInterface
            || $sale instanceof QuoteInterface;
    }

    /**
     * @inheritDoc
     */
    public function getPriority()
    {
        return 1024;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_commerce_sale_privacy';
    }
}