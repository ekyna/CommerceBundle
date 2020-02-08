<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Customer\Model\CustomerStates as States;

/**
 * Class CustomerStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CustomerStates extends AbstractConstants
{
    /**
     * @inheritDoc
     */
    static public function getConfig(): array
    {
        $prefix = 'ekyna_commerce.status.';

        return [
            States::STATE_NEW       => [$prefix . States::STATE_NEW,       'brown'],
            States::STATE_VALID     => [$prefix . States::STATE_VALID,     'light-green'],
            States::STATE_FRAUDSTER => [$prefix . States::STATE_FRAUDSTER, 'red'],
        ];
    }
}
