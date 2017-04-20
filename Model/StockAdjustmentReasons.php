<?php

declare(strict_types=1);

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
    public static function getConfig(): array
    {
        $prefix = 'stock_adjustment.reason.';

        return [
            Reasons::REASON_FAULTY   => [$prefix . Reasons::REASON_FAULTY],
            Reasons::REASON_IMPROPER => [$prefix . Reasons::REASON_IMPROPER],
            Reasons::REASON_DEBIT    => [$prefix . Reasons::REASON_DEBIT],
            Reasons::REASON_CREDIT   => [$prefix . Reasons::REASON_CREDIT],
            Reasons::REASON_FOUND    => [$prefix . Reasons::REASON_FOUND],
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
