<?php

namespace Ekyna\Bundle\CommerceBundle\Helper;

use Ekyna\Bundle\CommerceBundle\Model;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ConstantHelper
 * @package Ekyna\Bundle\CommerceBundle\Helper
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConstantHelper
{
    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
        if ($stateOrShipment instanceof PaymentInterface) {
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
        if ($stateOrShipment instanceof PaymentInterface) {
            $stateOrShipment = $stateOrShipment->getState();
        }

        $theme = 'default';
        if (Model\ShipmentStates::isValid($stateOrShipment)) {
            $theme = Model\ShipmentStates::getTheme($stateOrShipment);
        }

        return $this->renderBadge($this->renderShipmentStateLabel($stateOrShipment), $theme);
    }
}
