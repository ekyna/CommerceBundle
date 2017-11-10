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
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.document.type.';

        return [
            Types::TYPE_FORM         => [$prefix . types::TYPE_FORM, 'default', CartInterface::class],
            Types::TYPE_QUOTE        => [$prefix . types::TYPE_QUOTE, 'default', QuoteInterface::class],
            types::TYPE_PROFORMA     => [$prefix . types::TYPE_PROFORMA, 'default', OrderInterface::class],
            types::TYPE_CONFIRMATION => [$prefix . types::TYPE_CONFIRMATION, 'default', OrderInterface::class],
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
