<?php

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
    /**
     * @inheritDoc
     */
    static public function getConfig(): array
    {
        $prefix = 'ekyna_commerce.stock_subject.state.';

        return [
            States::STATE_IN_STOCK     => [$prefix . States::STATE_IN_STOCK,     'teal'],
            States::STATE_PRE_ORDER    => [$prefix . States::STATE_PRE_ORDER,    'orange'],
            States::STATE_OUT_OF_STOCK => [$prefix . States::STATE_OUT_OF_STOCK, 'red'],
        ];
    }
}
