<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Order\SubjectOrderExportType;
use Ekyna\Bundle\CommerceBundle\Model\SubjectOrderExport;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectOrderExporter;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Form\Extension\Core\Type;

/**
 * Class OrderController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderController extends SaleController
{
    /**
     * Prepare shipment action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function prepareAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        if ($sale instanceof ShipmentSubjectInterface) {
            $shipment = $this
                ->get('ekyna_commerce.sale_preparer')
                ->prepare($sale);

            if (null !== $shipment) {
                $this->getManager()->persist($sale);
                $this->getManager()->flush();

                $this->addFlash('ekyna_commerce.sale.prepare.success', 'success');
            } else {
                $this->addFlash('ekyna_commerce.sale.prepare.failure', 'warning');
            }
        }

        if (null !== $path = $request->query->get('_redirect')) {
            $redirect = $path;
        } elseif (null !== $referer = $request->headers->get('referer')) {
            $redirect = $referer;
        } else {
            $redirect = $this->generateResourcePath($sale);
        }

        return $this->redirect($redirect);
    }

    /**
     * Abort shipment action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function abortAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        if ($sale instanceof ShipmentSubjectInterface) {
            $shipment = $this
                ->get('ekyna_commerce.sale_preparer')
                ->abort($sale);

            if (null !== $shipment) {
                // TODO use shipment manager
                $this->getManager()->remove($shipment);
                $this->getManager()->flush();

                $this->addFlash('ekyna_commerce.sale.abort.success', 'success');
            }
        }

        if (null !== $path = $request->query->get('_redirect')) {
            $redirect = $path;
        } elseif (null !== $referer = $request->headers->get('referer')) {
            $redirect = $referer;
        } else {
            $redirect = $this->generateResourcePath($sale);
        }

        return $this->redirect($redirect);
    }

    /**
     * Prioritize action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function prioritizeAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        if ($sale instanceof ShipmentSubjectInterface) {
            $changed = $this
                ->get('ekyna_commerce.stock_prioritizer')
                ->prioritizeSale($sale);

            if ($changed) {
                $this->getManager()->flush();

                $this->addFlash('ekyna_commerce.sale.prioritize.success', 'success');
            } else {
                $this->addFlash('ekyna_commerce.sale.prioritize.failure', 'warning');
            }
        }

        if (null === $redirect = $request->query->get('_redirect')) {
            $redirect = $this->generateResourcePath($sale);
        }

        return $this->redirect($redirect);
    }

    /**
     * Toggle release action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function releaseAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw $this->createNotFoundException("Not yet supported.");
        }

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        if ($sale instanceof OrderInterface && $sale->isSample()) {
            $sale->setReleased(!$sale->isReleased());

            $event = $this->getOperator()->update($sale);
            $event->toFlashes($this->getFlashBag());
        }

        if (null !== $path = $request->query->get('_redirect')) {
            $redirect = $path;
        } elseif (null !== $referer = $request->headers->get('referer')) {
            $redirect = $referer;
        } else {
            $redirect = $this->generateResourcePath($sale);
        }

        return $this->redirect($redirect);
    }

    /**
     * Exports the orders to deliver.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function exportToDeliverAction(Request $request): Response
    {
        $this->isGranted('VIEW');

        $context = $this->loadContext($request);

        $data = new SubjectOrderExport();

        $form = $this->createForm(SubjectOrderExportType::class, $data);

        $form->add('actions', FormActionsType::class, [
            'buttons' => [
                'submit' => [
                    'type'    => Type\SubmitType::class,
                    'options' => [
                        'button_class' => 'primary',
                        'label'        => 'ekyna_core.button.export',
                        'attr'         => ['icon' => 'download'],
                    ],
                ],
                'cancel' => [
                    'type'    => Type\ButtonType::class,
                    'options' => [
                        'label'        => 'ekyna_core.button.cancel',
                        'button_class' => 'default',
                        'as_link'      => true,
                        'attr'         => [
                            'class' => 'form-cancel-btn',
                            'icon'  => 'remove',
                            'href'  => $this->generateUrl('ekyna_commerce_admin_order_list_shipment'),
                        ],
                    ],
                ],
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $path = $this->get(SubjectOrderExporter::class)->export($data);

            clearstatcache(true, $path);

            $stream = new Stream($path);
            $response = new BinaryFileResponse($stream);
            $response->headers->set('Content-Type', 'text/csv');
            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'orders-to-deliver.csv'
            );
            $response->headers->set('Content-Disposition', $disposition);

            return $response;
        }

        $this->appendBreadcrumb(
            sprintf('%s_export_to_deliver', $context->getConfiguration()->getResourceName()),
            'ekyna_commerce.order.header.export_to_deliver'
        );

        return $this->render('@EkynaCommerce/Admin/Order/export_to_deliver.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
