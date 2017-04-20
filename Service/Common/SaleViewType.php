<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\View;
use Ekyna\Component\Commerce\Common\View\LineView;
use Ekyna\Component\Commerce\Common\View\SaleView;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;


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
     * Sets the authorization checker.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker): void
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(Model\SaleInterface $sale, SaleView $view, array &$options): void
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

        if ($this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            return;
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
    public function buildSaleView(Model\SaleInterface $sale, View\SaleView $view, array $options): void
    {
        $view->setTranslations([
            'designation'    => $this->trans('field.designation', [], 'EkynaUi'),
            'reference'      => $this->trans('field.reference', [], 'EkynaUi'),
            'availability'   => $this->trans('sale.field.availability', [], 'EkynaCommerce'),
            'unit_net_price' => $this->trans('sale.field.net_unit', [], 'EkynaCommerce'),
            'unit_ati_price' => $this->trans('sale.field.ati_unit', [], 'EkynaCommerce'),
            'quantity'       => $this->trans('field.quantity', [], 'EkynaUi'),

            'net_gross'      => $this->trans('sale.field.net_gross', [], 'EkynaCommerce'),
            'ati_gross'      => $this->trans('sale.field.ati_gross', [], 'EkynaCommerce'),
            'discount'       => $this->trans('sale.field.discount', [], 'EkynaCommerce'),

            'tax_rate'       => $this->trans('sale.field.tax_rate', [], 'EkynaCommerce'),
            'tax_name'       => $this->trans('sale.field.tax_name', [], 'EkynaCommerce'),
            'tax_amount'     => $this->trans('sale.field.tax_amount', [], 'EkynaCommerce'),

            'gross_totals'   => $this->trans('sale.field.gross_totals', [], 'EkynaCommerce'),
            'net_total'      => $this->trans('sale.field.net_total', [], 'EkynaCommerce'),
            'tax_total'      => $this->trans('sale.field.tax_total', [], 'EkynaCommerce'),
            'ati_total'      => $this->trans('sale.field.ati_total', [], 'EkynaCommerce'),
            'margin'         => $this->trans('sale.field.margin', [], 'EkynaCommerce'),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function buildItemView(Model\SaleItemInterface $item, LineView $view, array $options): void
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
    public function buildAdjustmentView(Model\AdjustmentInterface $adjustment, View\LineView $view, array $options): void
    {
        if (!empty($adjustment->getDesignation())) {
            return;
        }

        $designation = $this->trans('adjustment.type.discount', [], 'EkynaCommerce');
        if ($adjustment->getMode() === Model\AdjustmentModes::MODE_PERCENT) {
            $designation .= ' ' . $this->formatter->percent($adjustment->getAmount());
        }

        $view->setDesignation($designation);
    }

    /**
     * @inheritDoc
     */
    public function buildShipmentView(Model\SaleInterface $sale, View\LineView $view, array $options): void
    {
        $result = $this->amountCalculatorFactory->create($options['currency'])->calculateSaleShipment($sale);

        if (0 >= $result->getTotal()) {
            $free = $this->trans('checkout.shipment.free_shipping', [], 'EkynaCommerce');
            $view->setBase($free);
            $view->setTotal($free);
        }

        if (!empty($sale->getShipmentLabel()) || !is_null($sale->getShipmentMethod())) {
            return;
        }

        $designation = $this->trans('sale.field.shipping_cost', [], 'EkynaCommerce');

        // Shipment weight
        $designation .= ' (' . $this->formatter->number($sale->getShipmentWeight() ?? $sale->getWeightTotal()) . ' kg)';

        $view->setDesignation($designation);
    }

    /**
     * @inheritDoc
     */
    public function supportsSale(Model\SaleInterface $sale): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'ekyna_commerce_sale';
    }
}
