<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Payment;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Quote\Repository\QuotePaymentRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class QuotePaymentController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class QuotePaymentController extends AbstractController
{
    /**
     * @var QuotePaymentRepositoryInterface
     */
    private $repository;


    /**
     * Sets the repository.
     *
     * @param QuotePaymentRepositoryInterface $repository
     */
    public function setRepository(QuotePaymentRepositoryInterface $repository)
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
            throw new NotFoundHttpException(sprintf('Failed to find QuotePayment with key "%s".', $key));
        }

        return $payment;
    }

    /**
     * @inheritDoc
     */
    protected function getDoneOptions()
    {
        return [
            'route' => 'ekyna_commerce_payment_quote_done',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function afterDone(Request $request, PaymentInterface $payment)
    {
        return $this->generateUrl('ekyna_commerce_quote_checkout_confirmation');
    }
}
