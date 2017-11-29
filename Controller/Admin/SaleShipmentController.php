<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Ekyna\Component\Commerce\Exception;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SaleShipmentController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleShipmentController extends AbstractSaleController
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

        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
        $shipment = $this->createNew($context);
        if ($request->query->get('return', 0)) {
            $shipment->setReturn(true);
        }

        $sale = $shipment->getSale();

        $context->addResource($resourceName, $shipment);

        $this->getOperator()->initialize($shipment);

        $form = $this->createNewResourceForm($context, !$isXhr, [
            'action' => $this->generateResourcePath($shipment, 'new', [
                'return' => $shipment->isReturn() ? 1 : 0,
            ]),
            'attr'   => [
                'class' => 'form-horizontal form-with-tabs',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->create($shipment);

            if (!$event->hasErrors()) {
                if ($isXhr) {
                    return $this->buildXhrSaleViewResponse($sale);
                } else {
                    $event->toFlashes($this->getFlashBag());
                }

                return $this->redirect($this->generateResourcePath($sale));
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $labelName = $shipment->isReturn() ? 'return' : 'shipment';

        if ($isXhr) {
            $modal = $this->createModal('new', 'ekyna_commerce.' . $labelName . '.header.new');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars());

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_new', $resourceName),
            'ekyna_commerce.' . $labelName . '.button.new'
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

        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
        $shipment = $context->getResource($resourceName);

        $this->isGranted('EDIT', $shipment);

        $isXhr = $request->isXmlHttpRequest();

        $form = $this->createEditResourceForm($context, !$isXhr, [
            'attr' => [
                'class' => 'form-horizontal form-with-tabs',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
            $sale = $context->getResource($this->getParentConfiguration()->getResourceName());

            // TODO use ResourceManager
            $event = $this->getOperator()->update($shipment);

            if (!$event->hasErrors()) {
                if ($isXhr) {
                    return $this->buildXhrSaleViewResponse($sale);
                } else {
                    $event->toFlashes($this->getFlashBag());
                }

                return $this->redirect($this->generateResourcePath($sale));
            }
            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $labelName = $shipment->isReturn() ? 'return' : 'shipment';

        if ($isXhr) {
            $modal = $this->createModal('new', 'ekyna_commerce.' . $labelName . '.header.edit');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars());

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_edit', $resourceName),
            'ekyna_commerce.' . $labelName . '.button.edit'
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
        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
        $shipment = $context->getResource($resourceName);

        $this->isGranted('DELETE', $shipment);

        $isXhr = $request->isXmlHttpRequest();
        $form = $this->createRemoveResourceForm($context, null, !$isXhr);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->delete($shipment);
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
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $labelName = $shipment->isReturn() ? 'return' : 'shipment';

        if ($isXhr) {
            $modal = $this->createModal('remove', 'ekyna_commerce.' . $labelName . '.header.remove');
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
            'ekyna_commerce.' . $labelName . '.button.remove'
        );

        return $this->render(
            $this->config->getTemplate('remove.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * Render action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderAction(Request $request)
    {
        $context = $this->loadContext($request);

        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
        $shipment = $context->getResource();

        $this->isGranted('VIEW', $shipment);

        $type = $request->attributes->get('type');

        $renderer = $this
            ->get('ekyna_commerce.document.renderer_factory')
            ->createRenderer($shipment, $type);

        return $renderer->respond($request);
    }

    /**
     * @inheritdoc
     */
    public function gatewayActionAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw $this->createNotFoundException("XmlHttpRequest is not supported.");
        }

        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
        $shipment = $context->getResource($resourceName);

        $this->isGranted('EDIT', $shipment);

        $helper = $this->get('ekyna_commerce.shipment_helper');

        try {
            $response = $helper->executeGatewayAction(
                $shipment->getGatewayName(),
                $request->attributes->get('action'),
                $shipment,
                $request
            );

            if (null !== $response) {
                return $response;
            }
        } catch (Exception\LogicException $e) {
            $this->addFlash('ekyna_commerce.shipment.message.unsupported_action', 'danger');
        } catch (Exception\RuntimeException $e) {
            $this->addFlash($e->getMessage(), 'danger');
        }

        if ($request->server->has('HTTP_REFERER') && !empty($referer = $request->server->get('HTTP_REFERER'))) {
            return $this->redirect($referer);
        }

        return $this->redirect($this->generateResourcePath($shipment));
    }
}
