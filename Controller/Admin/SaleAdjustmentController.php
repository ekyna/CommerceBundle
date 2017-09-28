<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SaleAdjustmentController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleAdjustmentController extends AbstractSaleController
{
    /**
     * {@inheritdoc}
     */
    public function homeAction()
    {
        throw new NotFoundHttpException();
    }

    /**
     * {@inheritdoc}
     */
    public function listAction(Request $request)
    {
        throw new NotFoundHttpException();
    }

    /**
     * {@inheritdoc}
     */
    public function showAction(Request $request)
    {
        throw new NotFoundHttpException();
    }

    /**
     * {@inheritdoc}
     */
    public function newAction(Request $request)
    {
        $this->isGranted('CREATE');

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($this->getParentConfiguration()->getResourceName());

        $isXhr = $request->isXmlHttpRequest();

        $adjustment = $this
            ->get('ekyna_commerce.sale_factory')
            ->createAdjustmentForSale($sale);
        $sale->addAdjustment($adjustment); // So that we can access to the sale from the adjustment.

        $context->addResource($resourceName, $adjustment);

        $this->getOperator()->initialize($adjustment);

        $form = $this->createNewResourceForm($context, !$isXhr, [
            'attr' => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->create($adjustment);
            if (!$isXhr) {
                $event->toFlashes($this->getFlashBag());
            }

            if (!$event->hasErrors()) {
                if ($isXhr) {
                    return $this->buildXhrSaleViewResponse($sale);
                }

                return $this->redirect($this->generateResourcePath($sale));
            } elseif ($isXhr) {
                // TODO all event messages should be bound to XHR response
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }
        }

        if ($isXhr) {
            $modal = $this->createModal('new', 'ekyna_commerce.sale.header.adjustment.new');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars());

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_new', $resourceName),
            'ekyna_commerce.sale.button.adjustment.new'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Adjustment:new.html.twig',
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function editAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        /** @var \Ekyna\Component\Commerce\Common\Model\AdjustmentInterface $adjustment */
        $adjustment = $context->getResource($resourceName);
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($this->getParentConfiguration()->getResourceName());

        if ($adjustment->isImmutable()) {
            throw new NotFoundHttpException('Adjustment is immutable.');
        }

        $this->isGranted('EDIT', $adjustment);

        $isXhr = $request->isXmlHttpRequest();

        $form = $this->createEditResourceForm($context, !$isXhr, [
            'attr' => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->update($adjustment);
            if (!$isXhr) {
                $event->toFlashes($this->getFlashBag());
            }

            if (!$event->hasErrors()) {
                if ($isXhr) {
                    return $this->buildXhrSaleViewResponse($sale);
                }

                return $this->redirect($this->generateResourcePath($sale));
            } elseif ($isXhr) {
                // TODO all event messages should be bound to XHR response
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }
        }

        if ($isXhr) {
            $modal = $this->createModal('new', 'ekyna_commerce.sale.header.adjustment.edit');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars());

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_configure', $resourceName),
            'ekyna_commerce.sale.button.adjustment.edit'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Adjustment:edit.html.twig',
            $context->getTemplateVars([
                'form'          => $form->createView(),
            ])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function removeAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        /** @var \Ekyna\Component\Commerce\Common\Model\AdjustmentInterface $adjustment */
        $adjustment = $context->getResource($resourceName);
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($this->getParentConfiguration()->getResourceName());

        if ($adjustment->isImmutable()) {
            throw new NotFoundHttpException('Adjustment is immutable.');
        }

        $this->isGranted('DELETE', $adjustment);

        $isXhr = $request->isXmlHttpRequest();

        // TODO confirmation form

        //if ($this->getSaleHelper()->removeSaleAdjustmentById($sale, $adjustment->getId())) {
            // TODO use ResourceManager
            $event = $this->getOperator()->delete($adjustment);
            if (!$isXhr) {
                $event->toFlashes($this->getFlashBag());
            }

            if (!$event->hasErrors()) {
                if ($isXhr) {
                    return $this->buildXhrSaleViewResponse($sale);
                }

                return $this->redirect($this->generateResourcePath($sale));
            } elseif ($isXhr) {
                // TODO all event messages should be bound to XHR response
                /*foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }*/
            }
        //}

        if ($isXhr) {
            return $this->buildXhrSaleViewResponse($sale);
        }

        $this->appendBreadcrumb(
            sprintf('%s_remove', $resourceName),
            'ekyna_commerce.sale.button.adjustment.remove'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Adjustment:remove.html.twig',
            $context->getTemplateVars([
                // TODO 'form' => $form->createView(),
            ])
        );
    }
}
