<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Payment;

use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderPaymentRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class OrderPaymentController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderPaymentController extends AbstractController
{
    /**
     * @var OrderPaymentRepositoryInterface
     */
    private $repository;


    /**
     * Sets the repository.
     *
     * @param OrderPaymentRepositoryInterface $repository
     */
    public function setRepository(OrderPaymentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    protected function findPaymentByRequest(Request $request)
    {
        $key = $request->attributes->get('key');

        $payment = $this->repository->findOneByKey($key);

        if (null === $payment) {
            throw new NotFoundHttpException(sprintf('Failed to find OrderPayment with key "%s".', $key));
        }

        return $payment;
    }

    /**
     * @inheritDoc
     */
    protected function getDoneOptions()
    {
        return [
            'route' => 'ekyna_commerce_payment_order_done',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function afterDone(Request $request, PaymentInterface $payment)
    {
        if ($payment instanceof OrderPaymentInterface) {
            $order = $payment->getOrder();

            return $this->redirect($this->generateUrl('ekyna_commerce_account_order_show', [
                'number' => $order->getNumber(),
            ]));
        }

        return $this->generateUrl('ekyna_commerce_account_order_index');
    }
}
