<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Order\Payment;

use Ekyna\Bundle\CommerceBundle\Service\Account\OrderResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\CommerceBundle\Service\Payment\CheckoutManager;
use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper;
use Ekyna\Bundle\UiBundle\Service\FlashHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidatorInterface;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

use function Symfony\Component\Translation\t;

/**
 * Class CreateController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Order\Payment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateController
{
    public function __construct(
        private readonly OrderResourceHelper        $resourceHelper,
        private readonly UrlGeneratorInterface      $urlGenerator,
        private readonly ConstantsHelper            $constantsHelper,
        private readonly FlashHelper                $flashHelper,
        private readonly SaleStepValidatorInterface $stepValidator,
        private readonly CheckoutManager            $checkoutManager,
        private readonly ResourceManagerInterface   $orderManager,
        private readonly PaymentHelper              $paymentHelper,
        private readonly Environment                $twig,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $customer = $this->resourceHelper->getCustomer();

        $order = $this->resourceHelper->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $redirect = $this->urlGenerator->generate('ekyna_commerce_account_order_read', [
            'number' => $order->getNumber(),
        ]);

        if ($customer->hasParent()) {
            $message = t('account.order.message.payment_denied', [
                '{identity}' => $this->constantsHelper->renderIdentity($customer->getParent()),
            ], 'EkynaCommerce');

            $this->flashHelper->addFlash($message, 'warning');

            return new RedirectResponse($redirect);
        }

        if (!$this->stepValidator->validate($order, SaleStepValidatorInterface::PAYMENT_STEP)) {
            return new RedirectResponse($redirect);
        }

        $action = $this->urlGenerator->generate('ekyna_commerce_account_order_payment_create', [
            'number' => $order->getNumber(),
        ]);

        $this->checkoutManager->initialize($order, $action);

        /** @var OrderPaymentInterface $payment */
        if ($payment = $this->checkoutManager->handleRequest($request)) {
            $order->addPayment($payment);

            $event = $this->orderManager->update($order);
            if ($event->isPropagationStopped() || $event->hasErrors()) {
                $this->flashHelper->fromEvent($event);

                return new RedirectResponse($redirect);
            }

            $statusUrl = $this->urlGenerator->generate(
                'ekyna_commerce_account_payment_status', [], UrlGeneratorInterface::ABSOLUTE_URL
            );

            return $this
                ->paymentHelper
                ->capture($payment, $statusUrl);
        }

        $orders = $this->resourceHelper->findOrdersByCustomer($customer);

        $content = $this->twig->render('@EkynaCommerce/Account/Order/payment_create.html.twig', [
            'customer' => $customer,
            'order'    => $order,
            'forms'    => $this->checkoutManager->getFormsViews(),
            'orders'   => $orders,
        ]);

        return (new Response($content))->setPrivate();
    }
}
