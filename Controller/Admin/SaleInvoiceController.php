<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SaleInvoiceController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleInvoiceController extends AbstractSaleController
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

        /** @var \Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface $invoice */
        $invoice = $this->createNew($context);

        $type = $request->attributes->get('type');
        if (!InvoiceTypes::isValidType($type)) {
            throw $this->createNotFoundException("Unexpected type");
        }

        $invoice->setType($type);

        if (0 < $shipmentId = $request->query->get('shipmentId', 0)) {
            // TODO find and assign shipment
        }

        $sale = $invoice->getSale();

        $context->addResource($resourceName, $invoice);

        $form = $this->createNewResourceForm($context, !$isXhr, [
            'action' => $this->generateResourcePath($invoice, 'new', [
                'type' => $invoice->getType(),
            ]),
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->create($invoice);

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
            $modal = $this->createModal('new', 'ekyna_commerce.' . $invoice->getType() . '.header.new');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars());

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_new', $resourceName),
            'ekyna_commerce.' . $invoice->getType() . '.button.new'
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

        /** @var \Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface $invoice */
        $invoice = $context->getResource($resourceName);

        $this->isGranted('EDIT', $invoice);

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
            $event = $this->getOperator()->update($invoice);

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
            $modal = $this->createModal('new', 'ekyna_commerce.' . $invoice->getType() . '.header.edit');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars());

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_edit', $resourceName),
            'ekyna_commerce.' . $invoice->getType() . '.button.edit'
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
        /** @var \Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface $invoice */
        $invoice = $context->getResource($resourceName);

        $this->isGranted('DELETE', $invoice);

        $isXhr = $request->isXmlHttpRequest();
        $form = $this->createRemoveResourceForm($context, null, !$isXhr);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->delete($invoice);
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
            $modal = $this->createModal('remove', 'ekyna_commerce.' . $invoice->getType() . '.header.remove');
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
            'ekyna_commerce.' . $invoice->getType() . '.button.remove'
        );

        return $this->render(
            $this->config->getTemplate('remove.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    public function renderAction(Request $request)
    {
        $context = $this->loadContext($request);

        /** @var \Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface $invoice */
        $invoice = $context->getResource();

        $this->isGranted('VIEW', $invoice);

        // TODO move in a renderer (that return a response)

        $response = new Response();
        if (!$this->getParameter('kernel.debug')) {
            $response->setLastModified($invoice->getUpdatedAt());
            if ($response->isNotModified($request)) {
                return $response;
            }
        }

        $content = $this->renderView('EkynaCommerceBundle:Invoice:render.html.twig', [
            'invoice' => $invoice,
        ]);

        $format = $request->attributes->get('_format', 'html');
        if ('html' === $format) {
            $response->setContent($content);
        } elseif ('pdf' === $format) {
            $response->setContent($this->get('knp_snappy.pdf')->getOutputFromHtml($content));
            $response->headers->add(['Content-Type' => 'application/pdf']);
        } else {
            throw new NotFoundHttpException('Unsupported format.');
        }

        if ($request->query->get('_download', false)) {
            $filename = sprintf('%s.%s', $invoice->getNumber(), $format);
            $contentDisposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename
            );
            $response->headers->set('Content-Disposition', $contentDisposition);
        }

        return $response;
    }
}
