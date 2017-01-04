<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Payum\Action\Sips;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;

/**
 * Class ConvertPaymentAction
 * @package Ekyna\Bundle\CommerceBundle\Service\Payum\Action\Sips
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConvertPaymentAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

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

        //$model['DESCRIPTION'] = $payment->getDescription();
        if (false == $model['amount']) {
            $this->gateway->execute($currency = new GetCurrency($payment->getCurrency()->getCode()));
            if (2 < $currency->exp) {
                throw new RuntimeException('Unexpected currency exp.');
            }
            $model['currency_code'] = $currency->numeric;
            // Amount in cents
            $model['amount'] = abs($payment->getAmount() * pow(10, $currency->exp));
        }

        if (false == $model['order_id']) {
            $model['order_id'] = $payment->getNumber();
        }

        $sale = $payment->getSale();
        if (false == $model['customer_id'] && null !== $customer = $sale->getCustomer()) {
            $model['customer_id'] = $customer->getId();
        }
        if (false == $model['customer_email']) {
            $model['customer_email'] = $sale->getEmail();
        }

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
