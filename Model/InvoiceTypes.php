<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes as Types;

/**
 * Class InvoiceTypes
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class InvoiceTypes extends AbstractConstants
{
    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.invoice.type.';
        $suffix = '.label';

        return [
            Types::TYPE_INVOICE => [$prefix . Types::TYPE_INVOICE . $suffix, 'success'],
            Types::TYPE_CREDIT  => [$prefix . Types::TYPE_CREDIT . $suffix,  'warning'],
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
