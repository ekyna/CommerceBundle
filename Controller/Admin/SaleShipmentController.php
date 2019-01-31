<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\GatewayDataType;
use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Ekyna\Component\Commerce\Exception\ShipmentGatewayException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
        $shipment = $context->getResource();

        /*if (ShipmentStates::isStockableState($shipment->getState())) {
            $this->addFlash('ekyna_commerce.shipment.message.edit_prevented', 'warning');

            return $this->redirect($this->generateResourcePath($shipment->getSale()));
        }*/

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
            sprintf('%s_edit', $this->config->getResourceName()),
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
     * Gateway data form action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function gatewayFormAction(Request $request)
    {
        $context = $this->loadContext($request);

        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface $method */
        $method = $this
            ->get('ekyna_commerce.shipment_method.repository')
            ->find($request->attributes->get('shipmentMethodId'));
        if (null === $method) {
            throw $this->createNotFoundException('Shipment method not found.');
        }

        if (!empty($shipmentId = $request->query->get('shipmentId'))) {
            $shipment = $this
                ->get('ekyna_commerce.order_shipment.repository')
                ->find(intval($shipmentId));
            if (null === $method) {
                throw $this->createNotFoundException('Shipment not found.');
            }
        } elseif (null === $isReturn = $request->query->get('return')) {
            throw $this->createNotFoundException("Expected 'shipmentId' or 'return' parameter.");
        } else {
            /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
            $shipment = $this->createNew($context);
            $shipment->setReturn((bool) $isReturn);
        }

        $shipment->setMethod($method);

        $form = $this
            ->get('form.factory')
            ->createNamed('order_shipment', FormType::class, $shipment)
            ->add('gatewayData', GatewayDataType::class);

        $response = $this->render('@EkynaCommerce/Admin/Common/Shipment/gateway_form.xml.twig', [
            'form' => $form->createView(),
        ]);

        $response->headers->add(['Content-Type' => 'application/xml']);

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function removeAction(Request $request)
    {
        $context = $this->loadContext($request);
        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
        $shipment = $context->getResource();

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
            sprintf('%s_remove', $this->config->getResourceName()),
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
     * Ship action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function shipAction(Request $request)
    {
        return $this->execute($request, 'ship');
    }

    /**
     * Cancel action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function cancelAction(Request $request)
    {
        return $this->execute($request, 'cancel');
    }

    /**
     * Complete action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function completeAction(Request $request)
    {
        return $this->execute($request, 'complete');
    }

    /**
     * Print label action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function printLabelAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw $this->createNotFoundException("XmlHttpRequest is not supported.");
        }

        $context = $this->loadContext($request);
        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
        $shipment = $context->getResource();

        $this->isGranted('EDIT', $shipment);

        $gateway = $this
            ->get('ekyna_commerce.shipment.gateway_registry')
            ->getGateway($shipment->getGatewayName());

        try {
            $labels = $gateway->printLabel($shipment, $request->query->get('types', null));

            $this->get('ekyna_commerce.shipment.persister')->flush();
        } catch (ShipmentGatewayException $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            if (empty($redirect = $request->headers->get('referer'))) {
                $redirect = $this->generateResourcePath($shipment->getSale());
            }

            return $this->redirect($redirect);
        }

        return $this
            ->get('ekyna_commerce.shipment.label_renderer')
            ->render($labels);
    }

    /**
     * Shipment summary action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function summaryAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $context = $this->loadContext($request);
        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
        $shipment = $context->getResource();

        $this->isGranted('VIEW', $shipment);

        $response = new Response();
        $response->setVary(['Accept', 'Accept-Encoding']);
        $response->setExpires(new \DateTime('+5 min'));

        $html = false;
        $accept = $request->getAcceptableContentTypes();

        if (in_array('application/json', $accept, true)) {
            $response->headers->add(['Content-Type' => 'application/json']);
        } elseif (in_array('text/html', $accept, true)) {
            $html = true;
        } else {
            throw $this->createNotFoundException("Unsupported content type.");
        }

        if ($html) {
            $content = $this->get('serializer')->normalize($shipment, 'json', ['groups' => ['Summary']]);
            $content = $this->renderView('@EkynaCommerce/Admin/Common/Shipment/summary.html.twig', $content);
        } else {
            $content = $this->get('serializer')->serialize($shipment, 'json', ['groups' => ['Summary']]);
        }

        $response->setContent($content);

        return $response;
    }

    /**
     * Executes the gateway action.
     *
     * @param Request $request
     * @param string  $action
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function execute(Request $request, string $action)
    {
        if ($request->isXmlHttpRequest()) {
            throw $this->createNotFoundException("XmlHttpRequest is not supported.");
        }

        $context = $this->loadContext($request);
        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
        $shipment = $context->getResource();

        $this->isGranted('EDIT', $shipment);

        $gateway = $this
            ->get('ekyna_commerce.shipment.gateway_registry')
            ->getGateway($shipment->getGatewayName());

        try {
            if ($gateway->{$action}($shipment)) {
                $this->get('ekyna_commerce.shipment.persister')->flush();
            }
        } catch (ShipmentGatewayException $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');
        }

        if (empty($redirect = $request->headers->get('referer'))) {
            $redirect = $this->generateResourcePath($shipment->getSale());
        }

        return $this->redirect($redirect);
    }
}
