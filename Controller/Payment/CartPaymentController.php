<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Payment;

use Ekyna\Bundle\CommerceBundle\Service\Common\SaleTransformerInterface;
use Ekyna\Component\Commerce\Cart\Model\CartPaymentInterface;
use Ekyna\Component\Commerce\Cart\Model\CartStates;
use Ekyna\Component\Commerce\Cart\Repository\CartPaymentRepositoryInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CartPaymentController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CartPaymentController extends AbstractController
{
    /**
     * @var CartPaymentRepositoryInterface
     */
    private $repository;

    /**
     * @var SaleTransformerInterface
     */
    private $saleTransformer;


    /**
     * Sets the repository.
     *
     * @param CartPaymentRepositoryInterface $repository
     */
    public function setRepository(CartPaymentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Sets the sale transformer.
     *
     * @param SaleTransformerInterface $transformer
     */
    public function setSaleTransformer(SaleTransformerInterface $transformer)
    {
        $this->saleTransformer = $transformer;
    }

    /**
     * @inheritDoc
     */
    protected function findPaymentByRequest(Request $request)
    {
        $key = $request->attributes->get('key');

        $payment = $this->repository->findOneByKey($key);

        if (null === $payment) {
            throw new NotFoundHttpException(sprintf('Failed to find CartPayment with key "%s".', $key));
        }

        return $payment;
    }

    /**
     * @inheritDoc
     */
    protected function getCaptureOptions()
    {
        return [
            'route' => 'ekyna_commerce_payment_cart_done',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function afterDone(Request $request, PaymentInterface $payment)
    {
        if (!$payment instanceof CartPaymentInterface) {
            throw new InvalidArgumentException('Expected instance of CartPaymentInterface');
        }

        $cart = $payment->getCart();

        // If cart is completed, transforms and redirect to order confirmation
        if (in_array($cart->getState(), [CartStates::STATE_ACCEPTED])) {
            $order = $this->saleTransformer->transformCartToOrder($cart, true);

            return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_confirmation', [
                'orderKey' => $order->getKey(),
            ]));
        }

        // Else go back to payments
        return $this->redirect($this->generateUrl('ekyna_commerce_cart_checkout_payment'));
    }
}
