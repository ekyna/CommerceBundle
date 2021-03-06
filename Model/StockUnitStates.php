<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates as States;

/**
 * Class StockUnitStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class StockUnitStates extends AbstractConstants
{
    /**
     * @inheritDoc
     */
    static public function getConfig(): array
    {
        $prefix = 'ekyna_commerce.status.';

        return [
            States::STATE_NEW     => [$prefix . States::STATE_NEW,     'brown',   false],
            States::STATE_PENDING => [$prefix . States::STATE_PENDING, 'orange',  false],
            States::STATE_READY   => [$prefix . States::STATE_READY,   'teal',    false],
            States::STATE_CLOSED  => [$prefix . States::STATE_CLOSED,  'default', false],
        ];
    }
}
