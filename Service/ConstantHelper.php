<?php

namespace Ekyna\Bundle\CommerceBundle\Service;

use Ekyna\Bundle\CommerceBundle\Model;
use Ekyna\Component\Commerce\Common\Model\IdentityInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ConstantHelper
 * @package Ekyna\Bundle\CommerceBundle\Service
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConstantHelper
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $gendersClass;


    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     * @param string              $gendersClass
     */
    public function __construct(TranslatorInterface $translator, $gendersClass)
    {
        $this->translator = $translator;
        $this->gendersClass = $gendersClass;
    }

    /**
     * Renders the state label.
     *
     * @param string $label
     *
     * @return string
     */
    private function renderLabel($label = 'ekyna_core.value.unknown')
    {
        return $this->translator->trans($label);
    }

    /**
     * Renders the state badge.
     *
     * @param string $label
     * @param string $theme
     *
     * @return string
     */
    private function renderBadge($label, $theme = 'default')
    {
        return sprintf('<span class="label label-%s">%s</span>', $theme, $label);
    }

    /**
     * Renders the product type label.
     *
     * @param ProductInterface|string $typeOrProduct
     *
     * @return string
     */
    public function renderProductTypeLabel($typeOrProduct)
    {
        if ($typeOrProduct instanceof ProductInterface) {
            $typeOrProduct = $typeOrProduct->getType();
        }

        if (Model\ProductTypes::isValid($typeOrProduct)) {
            return $this->renderLabel(Model\ProductTypes::getLabel($typeOrProduct));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the order state label.
     *
     * @param OrderInterface|string $stateOrOrder
     *
     * @return string
     */
    public function renderOrderStateLabel($stateOrOrder)
    {
        if ($stateOrOrder instanceof OrderInterface) {
            $stateOrOrder = $stateOrOrder->getState();
        }

        if (Model\OrderStates::isValid($stateOrOrder)) {
            return $this->renderLabel(Model\OrderStates::getLabel($stateOrOrder));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the order state badge.
     *
     * @param OrderInterface|string $stateOrOrder
     *
     * @return string
     */
    public function renderOrderStateBadge($stateOrOrder)
    {
        if ($stateOrOrder instanceof OrderInterface) {
            $stateOrOrder = $stateOrOrder->getState();
        }

        $theme = 'default';
        if (Model\OrderStates::isValid($stateOrOrder)) {
            $theme = Model\OrderStates::getTheme($stateOrOrder);
        }

        return $this->renderBadge($this->renderOrderStateLabel($stateOrOrder), $theme);
    }

    /**
     * Renders the quote state label.
     *
     * @param QuoteInterface|string $stateOrQuote
     *
     * @return string
     */
    public function renderQuoteStateLabel($stateOrQuote)
    {
        if ($stateOrQuote instanceof QuoteInterface) {
            $stateOrQuote = $stateOrQuote->getState();
        }

        if (Model\QuoteStates::isValid($stateOrQuote)) {
            return $this->renderLabel(Model\QuoteStates::getLabel($stateOrQuote));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the quote state badge.
     *
     * @param QuoteInterface|string $stateOrQuote
     *
     * @return string
     */
    public function renderQuoteStateBadge($stateOrQuote)
    {
        if ($stateOrQuote instanceof QuoteInterface) {
            $stateOrQuote = $stateOrQuote->getState();
        }

        $theme = 'default';
        if (Model\QuoteStates::isValid($stateOrQuote)) {
            $theme = Model\QuoteStates::getTheme($stateOrQuote);
        }

        return $this->renderBadge($this->renderQuoteStateLabel($stateOrQuote), $theme);
    }

    /**
     * Renders the sale state label.
     *
     * @param SaleInterface $sale
     *
     * @return string
     */
    public function renderSaleStateLabel(SaleInterface $sale)
    {
        if ($sale instanceof OrderInterface) {
            return $this->renderOrderStateLabel($sale);
        } elseif ($sale instanceof QuoteInterface) {
            return $this->renderQuoteStateLabel($sale);
        } else {
            throw new InvalidArgumentException("Unexpected sale.");
        }
    }

    /**
     * Renders the sale state badge.
     *
     * @param SaleInterface|string $sale
     *
     * @return string
     */
    public function renderSaleStateBadge(SaleInterface $sale)
    {
        if ($sale instanceof OrderInterface) {
            return $this->renderOrderStateBadge($sale);
        } elseif ($sale instanceof QuoteInterface) {
            return $this->renderQuoteStateBadge($sale);
        } else {
            throw new InvalidArgumentException("Unexpected sale.");
        }
    }

    /**
     * Renders the payment state label.
     *
     * @param PaymentInterface|string $stateOrPayment
     *
     * @return string
     */
    public function renderPaymentStateLabel($stateOrPayment)
    {
        if ($stateOrPayment instanceof PaymentInterface) {
            $stateOrPayment = $stateOrPayment->getState();
        }

        if (Model\PaymentStates::isValid($stateOrPayment)) {
            return $this->renderLabel(Model\PaymentStates::getLabel($stateOrPayment));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the payment state badge.
     *
     * @param PaymentInterface|string $stateOrPayment
     *
     * @return string
     */
    public function renderPaymentStateBadge($stateOrPayment)
    {
        if ($stateOrPayment instanceof PaymentInterface) {
            $stateOrPayment = $stateOrPayment->getState();
        }

        $theme = 'default';
        if (Model\PaymentStates::isValid($stateOrPayment)) {
            $theme = Model\PaymentStates::getTheme($stateOrPayment);
        }

        return $this->renderBadge($this->renderPaymentStateLabel($stateOrPayment), $theme);
    }

    /**
     * Renders the shipment state label.
     *
     * @param ShipmentInterface|string $stateOrShipment
     *
     * @return string
     */
    public function renderShipmentStateLabel($stateOrShipment)
    {
        if ($stateOrShipment instanceof ShipmentInterface) {
            $stateOrShipment = $stateOrShipment->getState();
        }

        if (Model\ShipmentStates::isValid($stateOrShipment)) {
            return $this->renderLabel(Model\ShipmentStates::getLabel($stateOrShipment));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the shipment state badge.
     *
     * @param ShipmentInterface|string $stateOrShipment
     *
     * @return string
     */
    public function renderShipmentStateBadge($stateOrShipment)
    {
        if ($stateOrShipment instanceof ShipmentInterface) {
            $stateOrShipment = $stateOrShipment->getState();
        }

        $theme = 'default';
        if (Model\ShipmentStates::isValid($stateOrShipment)) {
            $theme = Model\ShipmentStates::getTheme($stateOrShipment);
        }

        return $this->renderBadge($this->renderShipmentStateLabel($stateOrShipment), $theme);
    }

    /**
     * Renders the supplier order state label.
     *
     * @param SupplierOrderInterface|string $stateOrSupplierOrder
     *
     * @return string
     */
    public function renderSupplierOrderStateLabel($stateOrSupplierOrder)
    {
        if ($stateOrSupplierOrder instanceof SupplierOrderInterface) {
            $stateOrSupplierOrder = $stateOrSupplierOrder->getState();
        }

        if (Model\SupplierOrderStates::isValid($stateOrSupplierOrder)) {
            return $this->renderLabel(Model\SupplierOrderStates::getLabel($stateOrSupplierOrder));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the supplier order state badge.
     *
     * @param SupplierOrderInterface|string $stateOrSupplierOrder
     *
     * @return string
     */
    public function renderSupplierOrderStateBadge($stateOrSupplierOrder)
    {
        if ($stateOrSupplierOrder instanceof SupplierOrderInterface) {
            $stateOrSupplierOrder = $stateOrSupplierOrder->getState();
        }

        $theme = 'default';
        if (Model\SupplierOrderStates::isValid($stateOrSupplierOrder)) {
            $theme = Model\SupplierOrderStates::getTheme($stateOrSupplierOrder);
        }

        return $this->renderBadge($this->renderSupplierOrderStateLabel($stateOrSupplierOrder), $theme);
    }


    /**
     * Renders the identity.
     *
     * @param \Ekyna\Component\Commerce\Common\Model\IdentityInterface $identity
     * @param bool                                                     $long
     *
     * @return string
     */
    public function renderIdentity(IdentityInterface $identity, $long = false)
    {
        if (0 == strlen($identity->getFirstName()) && 0 == $identity->getLastName()) {
            return sprintf('<em>%s</em>', $this->translator->trans('ekyna_core.value.undefined'));
        }

        return sprintf(
            '%s %s %s',
            $this->translator->trans($this->getGenderLabel($identity->getGender(), $long)),
            $identity->getFirstName(),
            $identity->getLastName()
        );
    }

    /**
     * Returns the gender label.
     *
     * @param string $gender
     * @param bool $long
     *
     * @return mixed
     */
    public function getGenderLabel($gender, $long = false)
    {
        return call_user_func($this->gendersClass.'::getLabel', $gender, $long);
    }
}