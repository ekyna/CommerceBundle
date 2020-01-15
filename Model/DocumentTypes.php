<?php

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
    /**
     * @inheritDoc
     */
    static public function getConfig()
    {
        $document = 'ekyna_commerce.document.type.';

        return [
            Types::TYPE_FORM         => [$document . Types::TYPE_FORM,         'default', CartInterface::class],
            Types::TYPE_VOUCHER      => [$document . Types::TYPE_VOUCHER,      'default', QuoteInterface::class],
            Types::TYPE_QUOTE        => [$document . Types::TYPE_QUOTE,        'default', QuoteInterface::class],
            Types::TYPE_PROFORMA     => [$document . Types::TYPE_PROFORMA,     'default', OrderInterface::class],
            Types::TYPE_CONFIRMATION => [$document . Types::TYPE_CONFIRMATION, 'default', OrderInterface::class],
            Types::TYPE_INVOICE      => [$document . Types::TYPE_INVOICE,      'success', OrderInterface::class],
            Types::TYPE_CREDIT       => [$document . Types::TYPE_CREDIT,       'warning', OrderInterface::class],
        ];
    }

    /**
     * Returns the invoice types choices.
     *
     * @return array
     */
    public static function getInvoiceChoices(): array
    {
        return [
            'ekyna_commerce.document.type.' . Types::TYPE_INVOICE => Types::TYPE_INVOICE,
            'ekyna_commerce.document.type.' . Types::TYPE_CREDIT  => Types::TYPE_CREDIT,
        ];
    }

    /**
     * Returns the theme for the given type.
     *
     * @param string $type
     *
     * @return string
     */
    static public function getTheme($type)
    {
        static::isValid($type, true);

        return static::getConfig()[$type][1];
    }

    /**
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}
