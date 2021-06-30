<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Accounting;

use Ekyna\Bundle\CommerceBundle\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Accounting\Export\AbstractAccountingFilter;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;

/**
 * Class FactorAccountingFilter
 * @package Ekyna\Bundle\CommerceBundle\Service\Accounting
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @TODO Remove. Authorized payments are not exported anymore.
 */
class FactorAccountingFilter extends AbstractAccountingFilter
{
    /**
     * @inheritDoc
     *
     * Prevents export of factor payments with AUTHORIZED state.
     */
    public function filterPayment(PaymentInterface $payment): bool
    {
        if ($payment->getState() !== PaymentStates::STATE_AUTHORIZED) {
            return true;
        }

        $method = $payment->getMethod();

        if (!$method->isManual()) {
            return true;
        }

        if (!$method instanceof PaymentMethodInterface) {
            return true;
        }

        $config = $method->getConfig();
        if (isset($config['factor']) && $config['factor']) {
            return false;
        }

        return true;
    }
}
