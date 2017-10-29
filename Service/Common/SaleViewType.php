<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\View\LineView;
use Ekyna\Component\Commerce\Common\View\SaleView;

/**
 * Class SaleViewType
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleViewType extends AbstractViewType
{
    /**
     * @inheritDoc
     */
    public function buildSaleView(Model\SaleInterface $sale, SaleView $view, array $options)
    {
        $view->setTranslations([
            'designation'    => $this->trans('ekyna_core.field.designation'),
            'reference'      => $this->trans('ekyna_core.field.reference'),
            'unit_net_price' => $this->trans('ekyna_commerce.sale.field.net_unit'),
            'quantity'       => $this->trans('ekyna_core.field.quantity'),

            'tax_rate'       => $this->trans('ekyna_commerce.sale.field.tax_rate'),
            'tax_name'       => $this->trans('ekyna_commerce.sale.field.tax_name'),
            'tax_amount'     => $this->trans('ekyna_commerce.sale.field.tax_amount'),

            'gross_totals'   => $this->trans('ekyna_commerce.sale.field.gross_totals'),
            'net_total'      => $this->trans('ekyna_commerce.sale.field.net_total'),
            'tax_total'      => $this->trans('ekyna_commerce.sale.field.tax_total'),
            'grand_total'    => $this->trans('ekyna_commerce.sale.field.grand_total'),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function buildAdjustmentView(Model\AdjustmentInterface $adjustment, LineView $view, array $options)
    {
        if (!empty($adjustment->getDesignation())) {
            return;
        }

        $designation = $this->trans('ekyna_commerce.adjustment.type.discount');
        if ($adjustment->getMode() === Model\AdjustmentModes::MODE_PERCENT) {
            $designation .= ' ' . $this->formatter->percent($adjustment->getAmount());
        }

        $view->setDesignation($designation);
    }

    /**
     * @inheritDoc
     */
    public function buildShipmentView(Model\SaleInterface $sale, LineView $view, array $options)
    {
        if (null !== $sale->getPreferredShipmentMethod()) {
            return;
        }

        $designation = $this->trans('ekyna_commerce.sale.field.shipping_cost');

        // Total weight
        $designation .= ' (' . $this->formatter->number($sale->getWeightTotal()) . ' kg)';

        $view->setDesignation($designation);
    }

    /**
     * @inheritDoc
     */
    public function supportsSale(Model\SaleInterface $sale)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'ekyna_commerce_sale';
    }
}
