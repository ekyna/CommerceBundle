<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\CommerceBundle\Form\Type\Accounting\ExportType;
use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class ExportController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExportController extends Controller
{
    /**
     * Accounting export action
     *
     * @param Request $request
     *                        
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function accountingAction(Request $request)
    {
        $form = $this->createForm(ExportType::class);

        $form->handleRequest($request);

        if (!($form->isSubmitted() && $form->isValid())) {
            return $this->redirectToReferer($this->generateUrl('ekyna_admin_dashboard'));
        }

        $date = $form->get('date')->getData();

        try {
            $path = $this->get('ekyna_commerce.accounting.exporter')->export(new \DateTime($date));
        } catch (CommerceExceptionInterface $e) {
            return $this->doRedirect();
        }

        $filename = sprintf('accounting_%s.zip', $date);

        return $this->doRespond($path, $filename);
    }

    /**
     * Due orders export.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dueOrdersAction()
    {
        try {
            $path = $this
                ->get('ekyna_commerce.order.exporter')
                ->exportDueOrders();
        } catch (CommerceExceptionInterface $e) {
            return $this->doRedirect();
        }

        $filename = sprintf('due-order-%s.csv', (new \DateTime())->format('Y-m-d'));

        return $this->doRespond($path, $filename);
    }

    /**
     * Builds and returns the file response.
     *
     * @param string $path
     * @param string $filename
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function doRespond($path, $filename)
    {
        clearstatcache(true, $path);

        $stream = new Stream($path);
        $response = new BinaryFileResponse($stream);
        $response->headers->set('Content-Type', 'text/csv');
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * Builds and return the redirection.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function doRedirect()
    {
        return $this->redirectToReferer($this->generateUrl('ekyna_admin_dashboard'));
    }
}
