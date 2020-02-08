<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates as States;
use Ekyna\Bundle\ResourceBundle\Model\AbstractConstants;

/**
 * Class InvoiceStates
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class InvoiceStates extends AbstractConstants
{
    /**
     * @inheritDoc
     */
    static public function getConfig(): array
    {
        $prefix = 'ekyna_commerce.status.';

        return [
            States::STATE_NEW       => [$prefix.States::STATE_NEW,       'brown'],
            States::STATE_CANCELED  => [$prefix.States::STATE_CANCELED,  'default'],
            States::STATE_PENDING   => [$prefix.States::STATE_PENDING,   'orange'],
            States::STATE_PARTIAL   => [$prefix.States::STATE_PARTIAL,   'purple'],
            States::STATE_INVOICED  => [$prefix.States::STATE_INVOICED,  'teal'],
            States::STATE_CREDITED  => [$prefix.States::STATE_CREDITED,  'indigo'],
            States::STATE_COMPLETED => [$prefix.States::STATE_COMPLETED, 'teal'],
        ];
    }
}
