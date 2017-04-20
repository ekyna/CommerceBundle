<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates as States;

/**
 * Class StockStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class StockSubjectStates extends AbstractConstants
{
    public static function getConfig(): array
    {
        $prefix = 'stock_subject.state.';

        return [
            States::STATE_IN_STOCK     => [$prefix . States::STATE_IN_STOCK,     'teal'],
            States::STATE_PRE_ORDER    => [$prefix . States::STATE_PRE_ORDER,    'orange'],
            States::STATE_OUT_OF_STOCK => [$prefix . States::STATE_OUT_OF_STOCK, 'red'],
        ];
    }

    public static function getTranslationDomain(): ?string
    {
        return 'EkynaCommerce';
    }
}
