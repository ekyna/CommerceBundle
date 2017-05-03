<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SaleCreditController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleCreditController extends AbstractSaleController
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
        $this->isGranted('CREATE');

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        $isXhr = $request->isXmlHttpRequest();

        /** @var \Ekyna\Component\Commerce\Credit\Model\CreditInterface $credit */
        $credit = $this->createNew($context);
        if (0 < $shipmentId = $request->query->get('shipmentId', 0)) {
            // TODO find and assign shipment
        }

        $sale = $credit->getSale();

        $context->addResource($resourceName, $credit);

        $form = $this->createNewResourceForm($context, !$isXhr, [
            'action' => $this->generateResourcePath($credit, 'new'),
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->create($credit);

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
            $modal = $this->createModal('new', 'ekyna_commerce.credit.header.new');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars());

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_new', $resourceName),
            'ekyna_commerce.credit.button.new'
        );

        return $this->render(
            $this->config->getTemplate('new.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * @inheritdoc
     */
    public function editAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        /** @var \Ekyna\Component\Commerce\Credit\Model\CreditInterface $credit */
        $credit = $context->getResource($resourceName);

        $this->isGranted('EDIT', $credit);

        $isXhr = $request->isXmlHttpRequest();

        $form = $this->createEditResourceForm($context, !$isXhr, [
            'attr' => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
            $sale = $context->getResource($this->getParentConfiguration()->getResourceName());

            // TODO use ResourceManager
            $event = $this->getOperator()->update($credit);

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
            $modal = $this->createModal('new', 'ekyna_commerce.credit.header.edit');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars());

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_edit', $resourceName),
            'ekyna_commerce.credit.button.edit'
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
        /** @var \Ekyna\Component\Commerce\Credit\Model\CreditInterface $credit */
        $credit = $context->getResource($resourceName);

        $this->isGranted('DELETE', $credit);

        $isXhr = $request->isXmlHttpRequest();
        $form = $this->createRemoveResourceForm($context, null, !$isXhr);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->delete($credit);
            if (!$isXhr) {
                $event->toFlashes($this->getFlashBag());
            }

            if (!$event->hasErrors()) {
                /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
                $sale = $context->getResource($this->getParentConfiguration()->getResourceName());

                if ($isXhr) {
                    return $this->buildXhrSaleViewResponse($sale);
                } else {
                    $event->toFlashes($this->getFlashBag());
                }

                return $this->redirect($this->generateResourcePath($sale));
            } else {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }
        }

        if ($isXhr) {
            $modal = $this->createModal('remove', 'ekyna_commerce.credit.header.remove');
            $vars = $context->getTemplateVars();
            unset($vars['form_template']);
            $modal
                ->setSize(Modal::SIZE_NORMAL)
                ->setContent($form->createView())
                ->setVars($vars);

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_remove', $resourceName),
            'ekyna_commerce.credit.button.remove'
        );

        return $this->render(
            $this->config->getTemplate('remove.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }
}
