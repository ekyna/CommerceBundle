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
     * Returns the theme for the given state.
     *
     * @param string $state
     *
     * @return string
     */
    static public function getTheme($state)
    {
        static::isValid($state, true);

        return static::getConfig()[$state][1];
    }
}
