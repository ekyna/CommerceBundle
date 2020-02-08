<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates as States;

/**
 * Class SupplierOrderStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SupplierOrderStates extends AbstractConstants
{
    /**
     * {@inheritdoc}
     */
    static public function getConfig(): array
    {
        $prefix = 'ekyna_commerce.status.';

        return [
            States::STATE_NEW       => [$prefix . States::STATE_NEW,       'red'],
            States::STATE_ORDERED   => [$prefix . States::STATE_ORDERED,   'purple'],
            States::STATE_VALIDATED => [$prefix . States::STATE_VALIDATED, 'indigo'],
            States::STATE_PARTIAL   => [$prefix . States::STATE_PARTIAL,   'orange'],
            States::STATE_RECEIVED  => [$prefix . States::STATE_RECEIVED,  'light-green'],
            States::STATE_COMPLETED => [$prefix . States::STATE_COMPLETED, 'teal'],
            States::STATE_CANCELED  => [$prefix . States::STATE_CANCELED,  'default'],
        ];
    }
}
