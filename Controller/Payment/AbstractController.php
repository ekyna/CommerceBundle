<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Payment;

use Payum\Core\Payum;

/**
 * Class AbstractController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Payment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractController
{
    protected Payum $payum;

    public function __construct(Payum $payum)
    {
        $this->payum = $payum;
    }
}
