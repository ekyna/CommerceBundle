<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Order\Payment;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ControllerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Account\OrderResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper;
use Ekyna\Bundle\UiBundle\Form\Type\ConfirmType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

use function Symfony\Component\Translation\t;

/**
 * Class CancelController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Order\Payment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CancelController implements ControllerInterface
{
    public function __construct(
        private readonly OrderResourceHelper   $resourceHelper,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly PaymentHelper         $paymentHelper,
        private readonly FormFactoryInterface  $formFactory,
        private readonly Environment           $twig,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $customer = $this->resourceHelper->getCustomer();

        $order = $this->resourceHelper->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $payment = $this->resourceHelper->findPaymentByOrderAndKey($order, $request->attributes->get('key'));

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_order_read', [
            'number' => $order->getNumber(),
        ]);

        if (!$this->paymentHelper->isUserCancellable($payment)) {
            return new RedirectResponse($redirect);
        }

        $form = $this->formFactory->create(ConfirmType::class, null, [
            'message'     => t('account.payment.confirm_cancel', [
                '%number%' => $payment->getNumber(),
            ], 'EkynaCommerce'),
            'cancel_path' => $redirect,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $statusUrl = $this->urlGenerator->generate(
                'ekyna_commerce_account_payment_status', [], UrlGeneratorInterface::ABSOLUTE_URL
            );

            return $this->paymentHelper->cancel($payment, $statusUrl);
        }

        $orders = $this->resourceHelper->findOrdersByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Order/payment_cancel.html.twig', [
            'customer' => $customer,
            'order'    => $order,
            'form'     => $form->createView(),
            'orders'   => $orders,
        ]);

        return (new Response($content))->setPrivate();
    }
}
