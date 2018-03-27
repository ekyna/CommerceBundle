<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Payment\Model\PaymentTermTriggers as Triggers;

/**
 * Class PaymentTermTriggers
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentTermTriggers extends AbstractConstants
{
    /**
     * @inheritDoc
     */
    public static function getConfig()
    {
        $prefix = 'ekyna_commerce.payment_term.trigger.';

        return [
            Triggers::TRIGGER_SHIPPED        => [$prefix . Triggers::TRIGGER_SHIPPED],
            Triggers::TRIGGER_FULLY_SHIPPED  => [$prefix . Triggers::TRIGGER_FULLY_SHIPPED],
            Triggers::TRIGGER_INVOICED       => [$prefix . Triggers::TRIGGER_INVOICED],
            Triggers::TRIGGER_FULLY_INVOICED => [$prefix . Triggers::TRIGGER_FULLY_INVOICED],
        ];
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
