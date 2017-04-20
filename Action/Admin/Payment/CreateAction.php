<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Payment;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Action\Util\BreadcrumbTrait;
use Ekyna\Bundle\CommerceBundle\Service\Payment\CheckoutManager;
use Ekyna\Bundle\CommerceBundle\Service\Payment\PaymentHelper;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidatorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class CreateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Payment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateAction extends AbstractAction implements AdminActionInterface
{
    use FlashTrait;
    use HelperTrait;
    use ManagerTrait;
    use TemplatingTrait;
    use BreadcrumbTrait;

    private SaleStepValidatorInterface $stepValidator;
    private CheckoutManager            $checkoutManager;
    private PaymentHelper              $paymentHelper;

    public function __construct(
        SaleStepValidatorInterface $stepValidator,
        CheckoutManager            $checkoutManager,
        PaymentHelper              $paymentHelper
    ) {
        $this->stepValidator = $stepValidator;
        $this->checkoutManager = $checkoutManager;
        $this->paymentHelper = $paymentHelper;
    }

    public function __invoke(): Response
    {
        if ($this->request->isXmlHttpRequest()) {
            return new Response('XHR is not yet supported.', Response::HTTP_NOT_FOUND);
        }

        $sale = $this->context->getParentResource();

        if (!$sale instanceof SaleInterface) {
            throw new UnexpectedTypeException($sale, SaleInterface::class);
        }

        // TODO redirect (+ flash) if remaining amount <= 0

        if (!$this->stepValidator->validate($sale, SaleStepValidatorInterface::PAYMENT_STEP)) {
            $list = $this->stepValidator->getViolationList();

            $this->addFlashFromViolationList($list);

            return $this->redirect($this->generateResourcePath($sale));
        }

        $refund = $this->request->query->getBoolean('refund');

        $action = $this->generateResourcePath(
            $this->context->getConfig()->getId(),
            self::class,
            $this->request->query->all()
        );

        $this->checkoutManager->initialize($sale, $action, $refund, true);

        if (null !== $payment = $this->checkoutManager->handleRequest($this->request)) {
            $sale->addPayment($payment);
            $this->context->setResource($payment);

            $event = $this->getManager()->create($payment);

            $this->addFlashFromEvent($event);

            if ($event->hasErrors() || $event->isPropagationStopped()) {
                return $this->redirect($this->generateResourcePath($sale));
            }

            $statusUrl = $this->generateUrl(
                'admin_ekyna_commerce_payment_status',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            return $this->paymentHelper->capture($payment, $statusUrl);
        }

        $this->breadcrumbFromContext($this->context);

        return $this->render($this->options['template'], [
            'context' => $this->context,
            'refund'  => $refund,
            'sale'    => $sale,
            'forms'   => $this->checkoutManager->getFormsViews(),
        ]);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_payment_create',
            'permission' => Permission::CREATE,
            'route'      => [
                'name'    => 'admin_%s_create',
                'path'    => '/create',
                'methods' => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'payment.button.new',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'success',
                'icon'         => 'plus',
            ],
            'options'    => [
                'template' => '@EkynaCommerce/Admin/Common/Payment/create.html.twig',
            ],
        ];
    }
}
