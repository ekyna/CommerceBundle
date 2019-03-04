<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    public function refreshStockAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var SubjectInterface $subject */
        $subject = $context->getResource($resourceName);

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
     * Updates the subject's stock data.
     *
     * @param SubjectInterface $subject
     *
     * @return bool Whether the subject has been updated.
     */
    protected function updateStock(SubjectInterface $subject)
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
    public function labelAction(Request $request)
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