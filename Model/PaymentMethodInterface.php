<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\MediaBundle\Model\MediaSubjectInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface as BaseInterface;
use Payum\Core\Model\GatewayConfigInterface;

/**
 * Interface PaymentMethodInterface
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentMethodInterface extends BaseInterface, GatewayConfigInterface, MediaSubjectInterface
{

}
