<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentReasons as Reasons;
use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;

/**
 * Class StockAdjustmentReasons
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAdjustmentReasons extends AbstractConstants
{
    /**
     * @inheritdoc
     */
    static public function getConfig(): array
    {
        $prefix = 'ekyna_commerce.stock_adjustment.reason.';

        return [
            Reasons::REASON_FAULTY   => [$prefix . Reasons::REASON_FAULTY],
            Reasons::REASON_IMPROPER => [$prefix . Reasons::REASON_IMPROPER],
            Reasons::REASON_DEBIT    => [$prefix . Reasons::REASON_DEBIT],
            Reasons::REASON_CREDIT   => [$prefix . Reasons::REASON_CREDIT],
            Reasons::REASON_FOUND    => [$prefix . Reasons::REASON_FOUND],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getTheme(string $constant): ?string
    {
        return null;
    }
}
