<?php

declare(strict_types=1);

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
    public static function getConfig(): array
    {
        $prefix = 'payment_term.trigger.';

        return [
            Triggers::TRIGGER_SHIPPED        => [$prefix . Triggers::TRIGGER_SHIPPED],
            Triggers::TRIGGER_FULLY_SHIPPED  => [$prefix . Triggers::TRIGGER_FULLY_SHIPPED],
            Triggers::TRIGGER_INVOICED       => [$prefix . Triggers::TRIGGER_INVOICED],
            Triggers::TRIGGER_FULLY_INVOICED => [$prefix . Triggers::TRIGGER_FULLY_INVOICED],
        ];
    }

    public static function getTheme(string $constant): ?string
    {
        return null;
    }

    public static function getTranslationDomain(): ?string
    {
        return 'EkynaCommerce';
    }
}
