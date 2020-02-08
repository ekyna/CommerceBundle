<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Cart\Model\CartStates as States;

/**
 * Class CartStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CartStates extends AbstractConstants
{
    /**
     * @inheritDoc
     */
    static public function getConfig(): array
    {
        $prefix = 'ekyna_commerce.status.';

        return [
            States::STATE_NEW      => [$prefix . States::STATE_NEW,      'brown',       false],
            States::STATE_ACCEPTED => [$prefix . States::STATE_ACCEPTED, 'light-green', true],
        ];
    }
}
