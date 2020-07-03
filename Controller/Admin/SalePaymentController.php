<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidatorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class SalePaymentController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SalePaymentController extends AbstractSaleController
{
    /**
     * @inheritdoc
     */
    public function homeAction()
    {
        throw new NotFoundHttpException();
    }

    /**
     * @inheritdoc
     */
    public function listAction(Request $request)
    {
        throw new NotFoundHttpException();
    }

    /**
     * @inheritdoc
     */
    public function showAction(Request $request)
    {
        throw new NotFoundHttpException();
    }

    /**
     * @inheritdoc
     */
    public function newAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw $this->createNotFoundException("XHR is not yet supported.");
        }

        $this->isGranted('CREATE');

        $context      = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var SaleInterface $sale */
        $sale = $this->getParentResource($context);

        // TODO redirect (+ flash) if remaining amount <= 0

        $stepValidator = $this->get('ekyna_commerce.sale_step_validator');

        if (!$stepValidator->validate($sale, SaleStepValidatorInterface::PAYMENT_STEP)) {
            $list     = $stepValidator->getViolationList();
            $messages = [];

            /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
            foreach ($list as $violation) {
                $messages[] = $violation->getMessage();
            }

            if (!empty($messages)) {
                $this->addFlash(implode('<br>', $messages), 'danger');
            }

            return $this->redirect($this->generateResourcePath($sale));
        }

        $refund = (bool)$request->query->get('refund');

        $checkoutManager = $this->get('ekyna_commerce.payment.checkout_manager');

        $action = $this->generateResourcePath($this->config->getResourceId(), 'new', array_merge($context->getIdentifiers(), [
            'refund' => $refund ? 1 : 0,
        ]));

        $checkoutManager->initialize($sale, $action, $refund, true);

        if (null !== $payment = $checkoutManager->handleRequest($request)) {
            $sale->addPayment($payment);
            $context->addResource($resourceName, $payment);
            // TODO use ResourceManager
            $event = $this->getOperator()->create($payment);

            $event->toFlashes($this->getFlashBag());

            if ($event->hasErrors() || $event->isPropagationStopped()) {
                return $this->redirect($this->generateResourcePath($sale));
            }

            $statusUrl = $this->generateUrl(
                'ekyna_commerce_payment_admin_status',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            return $this
                ->get('ekyna_commerce.payment_helper')
                ->capture($payment, $statusUrl);
        }

        return $this->render(
            $this->config->getTemplate('new.html'),
            $context->getTemplateVars([
                'refund' => $refund,
                'sale'   => $sale,
                'forms'  => $checkoutManager->getFormsViews(),
            ])
        );
    }

    /**
     * @inheritdoc
     */
    public function editAction(Request $request)
    {
        $context      = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        /** @var \Ekyna\Component\Commerce\Payment\Model\PaymentInterface $payment */
        $payment = $context->getResource($resourceName);

        $this->isGranted('EDIT', $payment);

        $isXhr = $request->isXmlHttpRequest();

        $form = $this->createEditResourceForm($context, !$isXhr, [
            'attr' => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SaleInterface $sale */
            $sale = $context->getResource($this->getParentConfiguration()->getResourceName());

            // TODO use ResourceManager
            $event = $this->getOperator()->update($payment);

            if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }

            if ($isXhr) {
                return $this->buildXhrSaleViewResponse($sale);
            } else {
                $event->toFlashes($this->getFlashBag());
            }

            return $this->redirect($this->generateResourcePath($sale));
        }

        if ($isXhr) {
            $modal = $this->createModal('new', 'ekyna_commerce.payment.header.edit');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars());

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_configure', $resourceName),
            'ekyna_commerce.payment.button.edit'
        );

        return $this->render(
            $this->config->getTemplate('edit.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * @inheritdoc
     */
    public function removeAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Payment\Model\PaymentInterface $payment */
        $resource = $context->getResource($resourceName);

        $this->isGranted('DELETE', $resource);

        $isXhr = $request->isXmlHttpRequest();
        $form  = $this->createRemoveResourceForm($context, null, !$isXhr);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->delete($resource);
            if (!$isXhr) {
                $event->toFlashes($this->getFlashBag());
            }

            if (!$event->hasErrors()) {
                /** @var SaleInterface $sale */
                $sale = $context->getResource($this->getParentConfiguration()->getResourceName());

                if ($isXhr) {
                    return $this->buildXhrSaleViewResponse($sale);
                }

                return $this->redirect($this->generateResourcePath($sale));
            } else {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }
        }

        if ($isXhr) {
            $modal = $this->createModal('remove');
            $vars  = $context->getTemplateVars();
            unset($vars['form_template']);
            $modal
                ->setSize(Modal::SIZE_NORMAL)
                ->setContent($form->createView())
                ->setVars($vars);

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_remove', $resourceName),
            'ekyna_commerce.payment.button.remove'
        );

        return $this->render(
            $this->config->getTemplate('remove.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * @inheritdoc
     */
    public function actionAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw $this->createNotFoundException("XHR is not yet supported.");
        }

        $context      = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        /** @var \Ekyna\Component\Commerce\Payment\Model\PaymentInterface $payment */
        $payment = $context->getResource($resourceName);

        $this->isGranted('EDIT', $payment);

        $helper = $this->get('ekyna_commerce.payment_helper');

        $statusUrl = $this->generateUrl(
            'ekyna_commerce_payment_admin_status',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        switch ($request->attributes->get('action')) {
            case 'authorize' :
                return $helper->authorize($payment, $statusUrl);
            case 'accept' :
                return $helper->accept($payment, $statusUrl);
            case 'refund' :
                return $helper->refund($payment, $statusUrl);
            case 'reject' :
                return $helper->reject($payment, $statusUrl);
            case 'cancel' :
                return $helper->cancel($payment, $statusUrl);
            case 'hang' :
                return $helper->hang($payment, $statusUrl);
        }

        throw $this->createNotFoundException("Unexpected payment action.");
    }
}
