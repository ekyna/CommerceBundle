<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes as Types;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;

/**
 * Class DocumentTypes
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class DocumentTypes extends AbstractConstants
{
    private const LABEL_PREFIX = 'document.type.';


    public static function getConfig(): array
    {
        return [
            Types::TYPE_FORM          => [self::LABEL_PREFIX . Types::TYPE_FORM, 'default', CartInterface::class],
            Types::TYPE_VOUCHER       => [self::LABEL_PREFIX . Types::TYPE_VOUCHER, 'default', QuoteInterface::class],
            Types::TYPE_QUOTE         => [self::LABEL_PREFIX . Types::TYPE_QUOTE, 'default', QuoteInterface::class],
            Types::TYPE_PROFORMA      => [self::LABEL_PREFIX . Types::TYPE_PROFORMA, 'default', OrderInterface::class],
            Types::TYPE_CONFIRMATION  => [self::LABEL_PREFIX . Types::TYPE_CONFIRMATION, 'default', OrderInterface::class],

            Types::TYPE_INVOICE       => [self::LABEL_PREFIX . Types::TYPE_INVOICE, 'success', OrderInterface::class],
            Types::TYPE_CREDIT        => [self::LABEL_PREFIX . Types::TYPE_CREDIT, 'warning', OrderInterface::class],

            Types::TYPE_SHIPMENT_FORM => [self::LABEL_PREFIX . Types::TYPE_SHIPMENT_FORM, 'default', OrderInterface::class],
            Types::TYPE_SHIPMENT_BILL => [self::LABEL_PREFIX . Types::TYPE_SHIPMENT_BILL, 'default', OrderInterface::class],
        ];
    }

    /**
     * Returns the invoice types choices.
     *
     * @return array
     */
    public static function getSaleChoices(): array
    {
        return self::getChoices(Types::getSaleTypes(), self::FILTER_RESTRICT);
    }

    /**
     * Returns the invoice types choices.
     *
     * @return array
     */
    public static function getInvoiceChoices(): array
    {
        return self::getChoices(Types::getInvoiceTypes(), self::FILTER_RESTRICT);
    }

    /**
     * Returns the sale & invoice types choices.
     *
     * @return array
     */
    public static function getSaleAndInvoiceChoices(): array
    {
        return self::getChoices(Types::getSaleAndInvoiceTypes(), self::FILTER_RESTRICT);
    }

    /**
     * Returns the shipment types choices.
     *
     * @return array
     */
    public static function getShipmentChoices(): array
    {
        return self::getChoices(Types::getShipmentTypes(), self::FILTER_RESTRICT);
    }

    public static function getTranslationDomain(): ?string
    {
        return 'EkynaCommerce';
    }
}
