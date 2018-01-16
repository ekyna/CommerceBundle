<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates as States;
use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;

/**
 * Class InvoiceStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class InvoiceStates extends AbstractConstants
{
    /**
     * {@inheritdoc}
     */
    static public function getConfig()
    {
        $prefix = 'ekyna_commerce.invoice.state.';

        return [
            States::STATE_NEW      => [$prefix.States::STATE_NEW,      'default'],
            States::STATE_CANCELED => [$prefix.States::STATE_CANCELED, 'default'],
            States::STATE_PENDING  => [$prefix.States::STATE_PENDING,  'warning'],
            States::STATE_PARTIAL  => [$prefix.States::STATE_PARTIAL,  'warning'],
            States::STATE_INVOICED => [$prefix.States::STATE_INVOICED, 'success'],
            States::STATE_CREDITED => [$prefix.States::STATE_CREDITED, 'primary'],
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

    /**
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}
