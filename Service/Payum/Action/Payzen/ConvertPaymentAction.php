<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Payum\Action\Payzen;

use Ekyna\Bundle\CommerceBundle\Service\Payum\Action\AbstractAction;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;

/**
 * Class ConvertPaymentAction
 * @package Ekyna\Bundle\CommerceBundle\Service\Payum\Action\Payzen
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConvertPaymentAction extends AbstractAction
{
    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $model = ArrayObject::ensureArrayObject($payment->getDetails());

        if (false == $model['vads_amount']) {
            $this->gateway->execute($currency = new GetCurrency($payment->getCurrency()->getCode()));
            if (2 < $currency->exp) {
                throw new RuntimeException('Unexpected currency exp.');
            }

            $model['vads_currency'] = $currency->numeric;
            // Amount in cents
            $model['vads_amount'] = abs($payment->getAmount() * pow(10, $currency->exp));
        }

        /*if (false == $model['vads_order_id']) {
            $model['vads_order_id'] = $payment->getNumber();
        }
        if (false == $model['vads_cust_id']) {
            $model['vads_cust_id'] = $payment->getClientId();
        }
        if (false == $model['vads_cust_email']) {
            $model['vads_cust_email'] = $payment->getClientEmail();
        }*/

        $request->setResult((array)$model);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Convert
            && $request->getSource() instanceof PaymentInterface
            && $request->getTo() == 'array';
    }
}
