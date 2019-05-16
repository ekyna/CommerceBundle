<?php

namespace Ekyna\Bundle\CommerceBundle\Service;

use Ekyna\Bundle\CommerceBundle\Model;
use Ekyna\Bundle\ResourceBundle\Helper\AbstractConstantsHelper;
use Ekyna\Component\Commerce\Accounting\Model\AccountingInterface;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Payment\Model as Payment;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Commerce\Stock\Model as Stock;
use Ekyna\Component\Commerce\Supplier\Model as Supplier;
use Ekyna\Component\Commerce\Support\Model as Support;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ConstantsHelper
 * @package Ekyna\Bundle\CommerceBundle\Service
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConstantsHelper extends AbstractConstantsHelper
{
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
        parent::__construct($translator);

        $this->gendersClass = $gendersClass;
    }

    /**
     * Renders the accounting type label.
     *
     * @param AccountingInterface|string $accountingOrType
     *
     * @return string
     */
    public function renderAccountingTypeLabel($accountingOrType)
    {
        if ($accountingOrType instanceof AccountingInterface) {
            $accountingOrType = $accountingOrType->getType();
        }

        return $this->renderLabel(Model\AccountingTypes::getLabel($accountingOrType));
    }

    /**
     * Renders the customer state label.
     *
     * @param CustomerInterface|string $stateOrCustomer
     *
     * @return string
     */
    public function renderCustomerStateLabel($stateOrCustomer)
    {
        if ($stateOrCustomer instanceof CustomerInterface) {
            $stateOrCustomer = $stateOrCustomer->getState();
        }

        if (Model\CustomerStates::isValid($stateOrCustomer)) {
            return $this->renderLabel(Model\CustomerStates::getLabel($stateOrCustomer));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the customer state badge.
     *
     * @param CustomerInterface|string $stateOrCustomer
     *
     * @return string
     */
    public function renderCustomerStateBadge($stateOrCustomer)
    {
        if ($stateOrCustomer instanceof CustomerInterface) {
            $stateOrCustomer = $stateOrCustomer->getState();
        }

        $theme = 'default';
        if (Model\CustomerStates::isValid($stateOrCustomer)) {
            $theme = Model\CustomerStates::getTheme($stateOrCustomer);
        }

        return $this->renderBadge($this->renderCustomerStateLabel($stateOrCustomer), $theme);
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
     * Renders the cart state label.
     *
     * @param CartInterface|string $stateOrCart
     *
     * @return string
     */
    public function renderCartStateLabel($stateOrCart)
    {
        if ($stateOrCart instanceof CartInterface) {
            $stateOrCart = $stateOrCart->getState();
        }

        if (Model\CartStates::isValid($stateOrCart)) {
            return $this->renderLabel(Model\CartStates::getLabel($stateOrCart));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the cart state badge.
     *
     * @param CartInterface|string $stateOrCart
     *
     * @return string
     */
    public function renderCartStateBadge($stateOrCart)
    {
        if ($stateOrCart instanceof CartInterface) {
            $stateOrCart = $stateOrCart->getState();
        }

        $theme = 'default';
        if (Model\CartStates::isValid($stateOrCart)) {
            $theme = Model\CartStates::getTheme($stateOrCart);
        }

        return $this->renderBadge($this->renderCartStateLabel($stateOrCart), $theme);
    }

    /**
     * Renders the sale state label.
     *
     * @param Common\SaleInterface $sale
     *
     * @return string
     */
    public function renderSaleStateLabel(Common\SaleInterface $sale)
    {
        if ($sale instanceof OrderInterface) {
            return $this->renderOrderStateLabel($sale);
        } elseif ($sale instanceof QuoteInterface) {
            return $this->renderQuoteStateLabel($sale);
        } elseif ($sale instanceof CartInterface) {
            return $this->renderCartStateLabel($sale);
        } else {
            throw new InvalidArgumentException("Unexpected sale.");
        }
    }

    /**
     * Renders the sale state badge.
     *
     * @param Common\SaleInterface|string $sale
     *
     * @return string
     */
    public function renderSaleStateBadge(Common\SaleInterface $sale)
    {
        if ($sale instanceof OrderInterface) {
            return $this->renderOrderStateBadge($sale);
        } elseif ($sale instanceof QuoteInterface) {
            return $this->renderQuoteStateBadge($sale);
        } elseif ($sale instanceof CartInterface) {
            return $this->renderCartStateBadge($sale);
        } else {
            throw new InvalidArgumentException("Unexpected sale.");
        }
    }

    /**
     * Renders the invoice type label.
     *
     * @param Invoice\InvoiceInterface|string $typeOrInvoice
     *
     * @return string
     */
    public function renderInvoiceTypeLabel($typeOrInvoice)
    {
        if ($typeOrInvoice instanceof Invoice\InvoiceInterface) {
            $typeOrInvoice = $typeOrInvoice->getType();
        }

        if (Model\InvoiceTypes::isValid($typeOrInvoice)) {
            return $this->renderLabel(Model\InvoiceTypes::getLabel($typeOrInvoice));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the invoice type badge.
     *
     * @param Invoice\InvoiceInterface|string $typeOrInvoice
     *
     * @return string
     */
    public function renderInvoiceTypeBadge($typeOrInvoice)
    {
        if ($typeOrInvoice instanceof Invoice\InvoiceInterface) {
            $typeOrInvoice = $typeOrInvoice->getType();
        }

        $theme = 'default';
        if (Model\InvoiceTypes::isValid($typeOrInvoice)) {
            $theme = Model\InvoiceTypes::getTheme($typeOrInvoice);
        }

        return $this->renderBadge($this->renderInvoiceTypeLabel($typeOrInvoice), $theme);
    }

    /**
     * Renders the invoice state label.
     *
     * @param Invoice\InvoiceSubjectInterface|string $stateOrSubject
     *
     * @return string
     */
    public function renderInvoiceStateLabel($stateOrSubject)
    {
        if ($stateOrSubject instanceof Invoice\InvoiceSubjectInterface) {
            $stateOrSubject = $stateOrSubject->getInvoiceState();
        }

        if (Model\InvoiceStates::isValid($stateOrSubject)) {
            return $this->renderLabel(Model\InvoiceStates::getLabel($stateOrSubject));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the invoice state badge.
     *
     * @param Invoice\InvoiceSubjectInterface|string $stateOrSubject
     *
     * @return string
     */
    public function renderInvoiceStateBadge($stateOrSubject)
    {
        if ($stateOrSubject instanceof Invoice\InvoiceSubjectInterface) {
            $stateOrSubject = $stateOrSubject->getInvoiceState();
        }

        $theme = 'default';
        if (Model\InvoiceStates::isValid($stateOrSubject)) {
            $theme = Model\InvoiceStates::getTheme($stateOrSubject);
        }

        return $this->renderBadge($this->renderInvoiceStateLabel($stateOrSubject), $theme);
    }

    /**
     * Renders the notification type label.
     *
     * @param string $type
     *
     * @return string
     */
    public function renderNotifyTypeLabel(string $type)
    {
        if (Model\NotificationTypes::isValid($type)) {
            return $this->renderLabel(Model\NotificationTypes::getLabel($type));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the payment state label.
     *
     * @param Payment\PaymentInterface|Payment\PaymentSubjectInterface|string $stateOrPayment
     *
     * @return string
     */
    public function renderPaymentStateLabel($stateOrPayment)
    {
        if ($stateOrPayment instanceof Payment\PaymentInterface) {
            $stateOrPayment = $stateOrPayment->getState();
        } elseif ($stateOrPayment instanceof Payment\PaymentSubjectInterface) {
            $stateOrPayment = $stateOrPayment->getPaymentState();
        }

        if (Model\PaymentStates::isValid($stateOrPayment)) {
            return $this->renderLabel(Model\PaymentStates::getLabel($stateOrPayment));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the payment state badge.
     *
     * @param Payment\PaymentInterface|Payment\PaymentSubjectInterface|string $stateOrPayment
     *
     * @return string
     */
    public function renderPaymentStateBadge($stateOrPayment)
    {
        if ($stateOrPayment instanceof Payment\PaymentInterface) {
            $stateOrPayment = $stateOrPayment->getState();
        } elseif ($stateOrPayment instanceof Payment\PaymentSubjectInterface) {
            $stateOrPayment = $stateOrPayment->getPaymentState();
        }

        $theme = 'default';
        if (Model\PaymentStates::isValid($stateOrPayment)) {
            $theme = Model\PaymentStates::getTheme($stateOrPayment);
        }

        return $this->renderBadge($this->renderPaymentStateLabel($stateOrPayment), $theme);
    }

    /**
     * Renders the payment state label.
     *
     * @param Payment\PaymentTermInterface|string $termOrTrigger
     *
     * @return string
     */
    public function renderPaymentTermTriggerLabel($termOrTrigger)
    {
        if ($termOrTrigger instanceof Payment\PaymentTermInterface) {
            $termOrTrigger = $termOrTrigger->getTrigger();
        }

        if (Model\PaymentTermTriggers::isValid($termOrTrigger)) {
            return $this->renderLabel(Model\PaymentTermTriggers::getLabel($termOrTrigger));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the shipment state label.
     *
     * @param Shipment\ShipmentInterface|Shipment\ShipmentSubjectInterface|string $stateOrShipment
     *
     * @return string
     */
    public function renderShipmentStateLabel($stateOrShipment)
    {
        if ($stateOrShipment instanceof Shipment\ShipmentInterface) {
            $stateOrShipment = $stateOrShipment->getState();
        } elseif ($stateOrShipment instanceof Shipment\ShipmentSubjectInterface) {
            $stateOrShipment = $stateOrShipment->getShipmentState();
        }

        if (Model\ShipmentStates::isValid($stateOrShipment)) {
            return $this->renderLabel(Model\ShipmentStates::getLabel($stateOrShipment));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the shipment state badge.
     *
     * @param Shipment\ShipmentInterface|Shipment\ShipmentSubjectInterface|string $stateOrShipment
     *
     * @return string
     */
    public function renderShipmentStateBadge($stateOrShipment)
    {
        if ($stateOrShipment instanceof Shipment\ShipmentInterface) {
            $stateOrShipment = $stateOrShipment->getState();
        } elseif ($stateOrShipment instanceof Shipment\ShipmentSubjectInterface) {
            $stateOrShipment = $stateOrShipment->getShipmentState();
        }

        $theme = 'default';
        if (Model\ShipmentStates::isValid($stateOrShipment)) {
            $theme = Model\ShipmentStates::getTheme($stateOrShipment);
        }

        return $this->renderBadge($this->renderShipmentStateLabel($stateOrShipment), $theme);
    }

    /**
     * Renders the stock subject state label.
     *
     * @param Stock\StockSubjectInterface|string $stateOrStockSubject
     *
     * @return string
     */
    public function renderStockSubjectStateLabel($stateOrStockSubject)
    {
        if ($stateOrStockSubject instanceof Stock\StockSubjectInterface) {
            $stateOrStockSubject = $stateOrStockSubject->getStockState();
        }

        if (Model\StockSubjectStates::isValid($stateOrStockSubject)) {
            return $this->renderLabel(Model\StockSubjectStates::getLabel($stateOrStockSubject));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the stock subject state badge.
     *
     * @param Stock\StockSubjectInterface|string $stateOrStockSubject
     *
     * @return string
     */
    public function renderStockSubjectStateBadge($stateOrStockSubject)
    {
        if ($stateOrStockSubject instanceof Stock\StockSubjectInterface) {
            $stateOrStockSubject = $stateOrStockSubject->getStockState();
        }

        $theme = 'default';
        if (Model\StockSubjectStates::isValid($stateOrStockSubject)) {
            $theme = Model\StockSubjectStates::getTheme($stateOrStockSubject);
        }

        return $this->renderBadge($this->renderStockSubjectStateLabel($stateOrStockSubject), $theme);
    }

    /**
     * Renders the stock subject mode label.
     *
     * @param Stock\StockSubjectInterface|string $modeOrStockSubject
     *
     * @return string
     */
    public function renderStockSubjectModeLabel($modeOrStockSubject)
    {
        if ($modeOrStockSubject instanceof Stock\StockSubjectInterface) {
            $modeOrStockSubject = $modeOrStockSubject->getStockMode();
        }

        if (Model\StockSubjectModes::isValid($modeOrStockSubject)) {
            return $this->renderLabel(Model\StockSubjectModes::getLabel($modeOrStockSubject));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the stock subject mode badge.
     *
     * @param Stock\StockSubjectInterface|string $modeOrStockSubject
     *
     * @return string
     */
    public function renderStockSubjectModeBadge($modeOrStockSubject)
    {
        if ($modeOrStockSubject instanceof Stock\StockSubjectInterface) {
            $modeOrStockSubject = $modeOrStockSubject->getStockMode();
        }

        $theme = 'default';
        if (Model\StockSubjectModes::isValid($modeOrStockSubject)) {
            $theme = Model\StockSubjectModes::getTheme($modeOrStockSubject);
        }

        return $this->renderBadge($this->renderStockSubjectModeLabel($modeOrStockSubject), $theme);
    }

    /**
     * Renders the stock unit state label.
     *
     * @param Stock\StockUnitInterface|string $stateOrStockUnit
     *
     * @return string
     */
    public function renderStockUnitStateLabel($stateOrStockUnit)
    {
        if ($stateOrStockUnit instanceof Stock\StockUnitInterface) {
            $stateOrStockUnit = $stateOrStockUnit->getState();
        }

        if (Model\StockUnitStates::isValid($stateOrStockUnit)) {
            return $this->renderLabel(Model\StockUnitStates::getLabel($stateOrStockUnit));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the stock unit state badge.
     *
     * @param Stock\StockUnitInterface|string $stateOrStockUnit
     *
     * @return string
     */
    public function renderStockUnitStateBadge($stateOrStockUnit)
    {
        if ($stateOrStockUnit instanceof Stock\StockUnitInterface) {
            $stateOrStockUnit = $stateOrStockUnit->getState();
        }

        $theme = 'default';
        if (Model\StockUnitStates::isValid($stateOrStockUnit)) {
            $theme = Model\StockUnitStates::getTheme($stateOrStockUnit);
        }

        return $this->renderBadge($this->renderStockUnitStateLabel($stateOrStockUnit), $theme);
    }

    /**
     * Renders the stock adjustment reason label.
     *
     * @param Stock\StockAdjustmentInterface|string $adjustmentOrReason
     *
     * @return string
     */
    public function renderStockAdjustmentReasonLabel($adjustmentOrReason)
    {
        if ($adjustmentOrReason instanceof Stock\StockAdjustmentInterface) {
            $adjustmentOrReason = $adjustmentOrReason->getReason();
        }

        return $this->renderLabel(Model\StockAdjustmentReasons::getLabel($adjustmentOrReason));
    }

    /**
     * Renders the stock adjustment type label.
     *
     * @param Stock\StockAdjustmentInterface $adjustment
     *
     * @return string
     */
    public function renderStockAdjustmentTypeLabel(Stock\StockAdjustmentInterface $adjustment)
    {
        $debit = Stock\StockAdjustmentReasons::isDebitReason($adjustment->getReason());

        return $this->renderLabel('ekyna_commerce.stock_adjustment.field.' . ($debit ? 'debit' : 'credit'));
    }

    /**
     * Renders the stock adjustment type badge.
     *
     * @param Stock\StockAdjustmentInterface $adjustment
     *
     * @return string
     */
    public function renderStockAdjustmentTypeBadge(Stock\StockAdjustmentInterface $adjustment)
    {
        $debit = Stock\StockAdjustmentReasons::isDebitReason($adjustment->getReason());

        $theme = $debit ? 'danger' : 'success';

        return $this->renderBadge($this->renderStockAdjustmentTypeLabel($adjustment), $theme);
    }

    /**
     * Renders the supplier order state label.
     *
     * @param Supplier\SupplierOrderInterface|string $stateOrSupplierOrder
     *
     * @return string
     */
    public function renderSupplierOrderStateLabel($stateOrSupplierOrder)
    {
        if ($stateOrSupplierOrder instanceof Supplier\SupplierOrderInterface) {
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
     * @param Supplier\SupplierOrderInterface|string $stateOrSupplierOrder
     *
     * @return string
     */
    public function renderSupplierOrderStateBadge($stateOrSupplierOrder)
    {
        if ($stateOrSupplierOrder instanceof Supplier\SupplierOrderInterface) {
            $stateOrSupplierOrder = $stateOrSupplierOrder->getState();
        }

        $theme = 'default';
        if (Model\SupplierOrderStates::isValid($stateOrSupplierOrder)) {
            $theme = Model\SupplierOrderStates::getTheme($stateOrSupplierOrder);
        }

        return $this->renderBadge($this->renderSupplierOrderStateLabel($stateOrSupplierOrder), $theme);
    }

    /**
     * Renders the ticket state label.
     *
     * @param Support\TicketInterface|string $stateOrTicket
     *
     * @return string
     */
    public function renderTicketStateLabel($stateOrTicket)
    {
        if ($stateOrTicket instanceof Support\TicketInterface) {
            $stateOrTicket = $stateOrTicket->getState();
        }

        if (Model\TicketStates::isValid($stateOrTicket)) {
            return $this->renderLabel(Model\TicketStates::getLabel($stateOrTicket));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the ticket state badge.
     *
     * @param Support\TicketInterface|string $stateOrTicket
     *
     * @return string
     */
    public function renderTicketStateBadge($stateOrTicket)
    {
        if ($stateOrTicket instanceof Support\TicketInterface) {
            $stateOrTicket = $stateOrTicket->getState();
        }

        $theme = 'default';
        if (Model\TicketStates::isValid($stateOrTicket)) {
            $theme = Model\TicketStates::getTheme($stateOrTicket);
        }

        return $this->renderBadge($this->renderTicketStateLabel($stateOrTicket), $theme);
    }

    /**
     * Renders the supplier order attachment type label.
     *
     * @param Supplier\SupplierOrderAttachmentInterface|string $typeOrAttachment
     *
     * @return string
     */
    public function renderSupplierOrderAttachmentType($typeOrAttachment)
    {
        if ($typeOrAttachment instanceof Supplier\SupplierOrderAttachmentInterface) {
            $typeOrAttachment = $typeOrAttachment->getType();
        }

        if (Model\SupplierOrderAttachmentTypes::isValid($typeOrAttachment)) {
            return $this->renderLabel(Model\SupplierOrderAttachmentTypes::getLabel($typeOrAttachment));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the VAT display mode badge.
     *
     * @param string $vatDisplayMode
     *
     * @return string
     */
    public function renderVatDisplayModeBadge($vatDisplayMode)
    {
        if (null !== $vatDisplayMode) {
            $label = Model\VatDisplayModes::getLabel($vatDisplayMode);
            $theme = Model\VatDisplayModes::getTheme($vatDisplayMode);
        } else {
            $label = 'ekyna_core.field.default';
            $theme = 'default';
        }

        return $this->renderBadge($this->renderLabel($label), $theme);
    }

    /**
     * Renders the identity.
     *
     * @param Common\IdentityInterface $identity
     * @param bool                     $long
     *
     * @return string
     */
    public function renderIdentity(Common\IdentityInterface $identity, $long = false)
    {
        if (0 == strlen($identity->getFirstName()) && 0 == $identity->getLastName()) {
            return sprintf('<em>%s</em>', $this->translator->trans('ekyna_core.value.undefined'));
        }

        $gender = $identity->getGender()
            ? $this->translator->trans($this->getGenderLabel($identity->getGender(), $long))
            : null;

        return trim(sprintf('%s %s %s', $gender, $identity->getLastName(), $identity->getFirstName()));
    }

    /**
     * Renders the adjustment type label.
     *
     * @param string $type
     *
     * @return string
     */
    public function getAdjustmentTypeLabel($type)
    {
        if (Model\AdjustmentTypes::isValid($type)) {
            return $this->renderLabel(Model\AdjustmentTypes::getLabel($type));
        }

        return $this->renderLabel();
    }

    /**
     * Renders the adjustment mode label.
     *
     * @param string $mode
     *
     * @return string
     */
    public function getAdjustmentModeLabel($mode)
    {
        if (Model\AdjustmentModes::isValid($mode)) {
            return $this->renderLabel(Model\AdjustmentModes::getLabel($mode));
        }

        return $this->renderLabel();
    }

    /**
     * Returns the gender label.
     *
     * @param string $gender
     * @param bool   $long
     *
     * @return mixed
     */
    public function getGenderLabel($gender, $long = false)
    {
        return call_user_func($this->gendersClass . '::getLabel', $gender, $long);
    }
}
