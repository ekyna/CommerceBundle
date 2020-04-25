<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\View;
use Ekyna\Component\Commerce\Common\View\LineView;
use Ekyna\Component\Commerce\Common\View\SaleView;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;

/**
 * Class SaleViewType
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleViewType extends AbstractViewType
{
    /**
     * @var LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * @var AmountCalculatorFactory
     */
    private $amountCalculatorFactory;


    /**
     * Sets the locale provider.
     *
     * @param LocaleProviderInterface $provider
     */
    public function setLocaleProvider(LocaleProviderInterface $provider): void
    {
        $this->localeProvider = $provider;
    }

    /**
     * Sets the amount calculator factory.
     *
     * @param AmountCalculatorFactory $amountCalculatorFactory
     */
    public function setAmountCalculatorFactory(AmountCalculatorFactory $amountCalculatorFactory): void
    {
        $this->amountCalculatorFactory = $amountCalculatorFactory;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(Model\SaleInterface $sale, SaleView $view, array &$options)
    {
        if (!isset($options['locale'])) {
            $options['locale'] = $sale->getLocale() ?? $this->localeProvider->getCurrentLocale();
        }
        if (!isset($options['currency'])) {
            $options['currency'] = $sale->getCurrency()->getCode();
        }
        if (!isset($options['ati'])) {
            $options['ati'] = $sale->isAtiDisplayMode();
        }

        if ($sale->isReleased()) {
            $options['editable'] = false;
        }

        if ($sale->isLocked() && !(isset($options['private']) && $options['private'])) {
            $options['editable'] = false;
        }
    }

    /**
     * @inheritDoc
     */
    public function buildSaleView(Model\SaleInterface $sale, View\SaleView $view, array $options)
    {
        $view->setTranslations([
            'designation'    => $this->trans('ekyna_core.field.designation'),
            'reference'      => $this->trans('ekyna_core.field.reference'),
            'availability'   => $this->trans('ekyna_commerce.sale.field.availability'),
            'unit_net_price' => $this->trans('ekyna_commerce.sale.field.net_unit'),
            'unit_ati_price' => $this->trans('ekyna_commerce.sale.field.ati_unit'),
            'quantity'       => $this->trans('ekyna_core.field.quantity'),

            'net_gross'      => $this->trans('ekyna_commerce.sale.field.net_gross'),
            'ati_gross'      => $this->trans('ekyna_commerce.sale.field.ati_gross'),
            'discount'       => $this->trans('ekyna_commerce.sale.field.discount'),

            'tax_rate'       => $this->trans('ekyna_commerce.sale.field.tax_rate'),
            'tax_name'       => $this->trans('ekyna_commerce.sale.field.tax_name'),
            'tax_amount'     => $this->trans('ekyna_commerce.sale.field.tax_amount'),

            'gross_totals'   => $this->trans('ekyna_commerce.sale.field.gross_totals'),
            'net_total'      => $this->trans('ekyna_commerce.sale.field.net_total'),
            'tax_total'      => $this->trans('ekyna_commerce.sale.field.tax_total'),
            'ati_total'      => $this->trans('ekyna_commerce.sale.field.ati_total'),
            'margin'         => $this->trans('ekyna_commerce.sale.field.margin'),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function buildItemView(Model\SaleItemInterface $item, LineView $view, array $options)
    {
        if (!$item->hasSubjectIdentity()) {
            return;
        }

        $url = $options['private'] ? $this->getPrivateUrl($item) : $this->getPublicUrl($item);
        if (empty($url)) {
            return;
        }

        $link = [
            'href'  => $url,
            'title' => $item->getDesignation(),
        ];
        if (isset($view->vars['link'])) {
            $view->vars['link'] = array_replace($view->vars['link'], $link);
        } else {
            $view->vars['link'] = $link;
        }
    }

    /**
     * @inheritDoc
     */
    public function buildAdjustmentView(Model\AdjustmentInterface $adjustment, View\LineView $view, array $options)
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
    public function buildShipmentView(Model\SaleInterface $sale, View\LineView $view, array $options)
    {
        $result = $this->amountCalculatorFactory->create($options['currency'])->calculateSaleShipment($sale);

        if (0 >= $result->getTotal()) {
            $free = $this->trans('ekyna_commerce.checkout.shipment.free_shipping');
            $view->setBase($free);
            $view->setTotal($free);
        }

        if (!empty($sale->getShipmentLabel()) || !is_null($sale->getShipmentMethod())) {
            return;
        }

        $designation = $this->trans('ekyna_commerce.sale.field.shipping_cost');

        // Shipment weight
        $designation .= ' (' . $this->formatter->number($sale->getShipmentWeight() ?? $sale->getWeightTotal()) . ' kg)';

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
