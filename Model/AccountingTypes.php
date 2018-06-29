<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Accounting\Model\AccountingTypes as Types;
use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;

/**
 * Class AccountingTypes
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AccountingTypes extends AbstractConstants
{
    /**
     * @inheritDoc
     */
    public static function getConfig()
    {
        $prefix = 'ekyna_commerce.accounting.type.';

        return [
            Types::TYPE_GOOD     => [$prefix . Types::TYPE_GOOD],
            //Types::TYPE_SERVICE  => [$prefix . Types::TYPE_SERVICE],
            Types::TYPE_SHIPPING => [$prefix . Types::TYPE_SHIPPING],
            Types::TYPE_TAX      => [$prefix . Types::TYPE_TAX],
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
