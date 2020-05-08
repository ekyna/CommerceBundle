<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CommerceBundle\Model\SubjectOrderExport;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectOrderExporter;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AbstractSubjectController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSubjectController extends ResourceController
{
    /**
     * Refresh stock action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function refreshStockAction(Request $request): Response
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        $subject = $context->getResource($resourceName);
        if (!$subject instanceof SubjectInterface) {
            throw new NotFoundHttpException();
        }

        $this->isGranted('VIEW', $subject);

        if (!$request->query->get('no-update', false)) {
            $this->isGranted('EDIT', $subject);

            if ($this->updateStock($subject)) {
                $event = $this->getOperator()->update($subject);
                if ($event->hasErrors()) {
                    throw new RuntimeException("Failed to update subject stock data.");
                }
            }
        }

        $response = $this->render('@EkynaCommerce/Admin/Subject/response.xml.twig', [
            'subject'    => $subject,
            'stock_view' => true,
        ]);

        $response->headers->add(['Content-Type' => 'application/xml']);

        return $response;

//        /** @var StockSubjectInterface $subject */
//        TODO return $this->createStockViewResponse($subject);
    }

    /**
     * Exports the orders that needs to be shipped for this subject.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function exportOrders(Request $request): Response
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        $subject = $context->getResource($resourceName);
        if (!$subject instanceof StockSubjectInterface) {
            throw new NotFoundHttpException();
        }

        $this->isGranted('VIEW', $subject);

        $data = new SubjectOrderExport();
        $data->addSubject($subject);

        $path = $this->get(SubjectOrderExporter::class)->export($data);

        clearstatcache(true, $path);

        $stream = new Stream($path);
        $response = new BinaryFileResponse($stream);
        $response->headers->set('Content-Type', 'text/csv');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $subject->getReference() . '_pending-orders.csv'
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * Updates the subject's stock data.
     *
     * @param SubjectInterface $subject
     *
     * @return bool Whether the subject has been updated.
     */
    protected function updateStock(SubjectInterface $subject): bool
    {
        if (!$subject instanceof StockSubjectInterface) {
            return false;
        }

        return $this->get('ekyna_commerce.stock_subject_updater')->update($subject);
    }

    /**
     * Label action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function labelAction(Request $request): Response
    {
        $this->isGranted('VIEW');

        $format = $request->attributes->get('format');
        $ids = (array)$request->query->get('id', []);

        $ids = array_map(function ($id) {
            return intval($id);
        }, $ids);

        $ids = array_filter($ids, function ($id) {
            return 0 < $id;
        });

        $products = (array)$this->getRepository()->findBy(['id' => $ids]);

        $renderer = $this->get('ekyna_commerce.subject.label_renderer');

        $labels = $renderer->buildLabels($products);

        $pdf = $renderer->render($labels, $format);

        return new Response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
