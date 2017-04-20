<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Payment;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\UrlGeneratorTrait;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentTransitions;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;

/**
 * Class ActionAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Payment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ActionAction extends AbstractAction implements AdminActionInterface, RoutingActionInterface
{
    use UrlGeneratorTrait;

    private PaymentHelper $paymentHelper;

    public function __construct(PaymentHelper $paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;
    }

    public function __invoke(): Response
    {
        if ($this->request->isXmlHttpRequest()) {
            return new Response('XHR is not yet supported.', Response::HTTP_NOT_FOUND);
        }

        $payment = $this->context->getResource();

        if (!$payment instanceof PaymentInterface) {
            throw new UnexpectedTypeException($payment, PaymentInterface::class);
        }

        $statusUrl = $this->generateUrl(
            'admin_ekyna_commerce_payment_status',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        switch ($this->request->attributes->get('action')) {
            case PaymentTransitions::TRANSITION_AUTHORIZE :
                return $this->paymentHelper->authorize($payment, $statusUrl);
            case PaymentTransitions::TRANSITION_ACCEPT :
                return $this->paymentHelper->accept($payment, $statusUrl);
            case PaymentTransitions::TRANSITION_PAYOUT :
                return $this->paymentHelper->payout($payment, $statusUrl);
            case PaymentTransitions::TRANSITION_REFUND :
                return $this->paymentHelper->refund($payment, $statusUrl);
            case PaymentTransitions::TRANSITION_REJECT :
                return $this->paymentHelper->reject($payment, $statusUrl);
            case PaymentTransitions::TRANSITION_CANCEL :
                return $this->paymentHelper->cancel($payment, $statusUrl);
            case PaymentTransitions::TRANSITION_HANG :
                return $this->paymentHelper->hang($payment, $statusUrl);
        }

        throw new UnexpectedValueException('Unexpected payment action.');
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_payment_action',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_action',
                'path'     => '/action/{action}',
                'resource' => true,
                'methods'  => ['GET'],
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'action' => 'cancel|authorize|accept|payout|hang|reject|refund',
        ]);
    }
}
