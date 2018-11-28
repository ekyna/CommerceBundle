<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Ekyna\Bundle\CommerceBundle\Model\InvoiceTypes as BInvoiceTypes;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes as CInvoiceTypes;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $context->addResource('resource', $invoice);

        $type = $request->attributes->get('type');
        if (!CInvoiceTypes::isValidType($type)) {
            throw $this->createNotFoundException("Unexpected type");
        }

        $invoice->setType($type);

        /*if (0 < $shipmentId = $request->query->get('shipmentId', 0)) {
            // TODO find and assign shipment
        }*/

        $sale = $invoice->getSale();

        $context->addResource($resourceName, $invoice);

        $this->getOperator()->initialize($invoice);

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

        if ($invoice->getShipment()) {
            $message =  'ekyna_commerce.invoice.alert.' . $invoice->getType() . '_edit_prevented';

            $this->addFlash($message, 'warning');

            return $this->redirect($this->generateResourcePath($invoice->getSale()));
        }

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

    /**
     * Recalculate action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function recalculateAction(Request $request)
    {
        $context = $this->loadContext($request);

        /** @var \Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface $invoice */
        $invoice = $context->getResource();

        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($this->getParentConfiguration()->getResourceName());

        $this->isGranted('EDIT', $invoice);

        if ($isXhr = $request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not yet supported.');
        }

        // Create an archived version of this invoice before recalculation
        $renderer = $this
            ->get('ekyna_commerce.document.renderer_factory')
            ->createRenderer($invoice);

        $path = $renderer->create();

        $this->getTranslator()->trans(BInvoiceTypes::getLabel($invoice->getType()));

        /** @var \Ekyna\Component\Resource\Configuration\ConfigurationInterface $config */
        $config = $this->get(str_replace('invoice', 'attachment', $this->config->getResourceId()) . '.configuration');
        /** @var \Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository $repository */
        $repository = $this->get($config->getServiceKey('repository'));
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleAttachmentInterface $attachment */
        $attachment = $repository->createNew();

        $title = sprintf(
            '[archived] %s %s',
            $this->getTranslator()->trans(BInvoiceTypes::getLabel($invoice->getType())),
            $invoice->getNumber()
        );

        $filename = sprintf('%s-%s.pdf', $invoice->getType(), $invoice->getNumber());

        $attachment
            ->setSale($sale)
            ->setTitle($title)
            ->setFile(new File($path))
            ->setRename($filename)
            ->setInternal(true);

        /** @var \Ekyna\Component\Resource\Operator\ResourceOperatorInterface $operator */
        $operator = $this->get($config->getServiceKey('operator'));

        $event = $operator->create($attachment);
        if ($event->hasErrors()) {
            $event->toFlashes($this->getFlashBag());

            return $this->redirect($this->generateResourcePath($sale));
        }

        // Recalculate
        $this->get('ekyna_commerce.document.calculator')->calculate($invoice);

        // TODO use ResourceManager
        $event = $this->getOperator()->update($invoice);

        if ($isXhr) {
            return $this->buildXhrSaleViewResponse($sale);
        } else {
            $event->toFlashes($this->getFlashBag());
        }

        return $this->redirect($this->generateResourcePath($sale));
    }

    /**
     * Unlink (from shipment) action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function unlinkAction(Request $request)
    {
        $context = $this->loadContext($request);

        /** @var \Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface $invoice */
        $invoice = $context->getResource();
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($this->getParentConfiguration()->getResourceName());

        $this->isGranted('EDIT', $invoice);

        if ($isXhr = $request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not yet supported.');
        }

        if (null !== $shipment = $invoice->getShipment()) {
            $shipment
                ->setAutoInvoice(false)
                ->setInvoice(null);

            $em = $this->get('doctrine.orm.default_entity_manager');
            $em->persist($shipment);
            $em->flush($shipment);
        }

        if ($isXhr) {
            return $this->buildXhrSaleViewResponse($sale);
        }

        return $this->redirect($this->generateResourcePath($sale));
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

        /** @var \Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface $invoice */
        $invoice = $context->getResource();

        $this->isGranted('VIEW', $invoice);

        $renderer = $this
            ->get('ekyna_commerce.document.renderer_factory')
            ->createRenderer($invoice);

        return $renderer->respond($request);
    }

    /**
     * Invoice summary action.
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
        /** @var \Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface $invoice */
        $invoice = $context->getResource();

        $this->isGranted('VIEW', $invoice);

        $response = new Response();
        $response->setVary(['Accept', 'Accept-Encoding']);
        $response->setLastModified($invoice->getUpdatedAt());

        $html = false;
        $accept = $request->getAcceptableContentTypes();

        if (in_array('application/json', $accept, true)) {
            $response->headers->add(['Content-Type' => 'application/json']);
        } elseif (in_array('text/html', $accept, true)) {
            $html = true;
        } else {
            throw $this->createNotFoundException("Unsupported content type.");
        }

        if ($response->isNotModified($request)) {
            return $response;
        }

        if ($html) {
            $content = $this->get('serializer')->normalize($invoice, 'json', ['groups' => ['Summary']]);
            $content = $this->renderView('@EkynaCommerce/Admin/Common/Invoice/summary.html.twig', $content);
        } else {
            $content = $this->get('serializer')->serialize($invoice, 'json', ['groups' => ['Summary']]);
        }

        $response->setContent($content);

        return $response;
    }
}
