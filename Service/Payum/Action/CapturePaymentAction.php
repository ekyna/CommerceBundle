<?php
namespace Ekyna\Bundle\CommerceBundle\Service\Payum\Action;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetHumanStatus;

/**
 * Class CapturePaymentAction
 * @package Ekyna\Bundle\CommerceBundle\Service\Payum\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CapturePaymentAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @inheritdoc
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();

        $this->gateway->execute($status = new GetHumanStatus($payment));
        if ($status->isNew()) {
            $this->gateway->execute($convert = new Convert($payment, 'array', $request->getToken()));

            $payment->setDetails($convert->getResult());
        }

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $request->setModel($details);
        try {
            $this->gateway->execute($request);
        } finally {
            $payment->setDetails($details);
        }
    }

    /**
     * @inheritdoc
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof PaymentInterface
        ;
    }
}
