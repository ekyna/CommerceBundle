<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\CommerceBundle\Form\Type\Accounting\ExportType;
use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @return Response
     */
    public function accountingAction(Request $request)
    {
        $form = $this->createForm(ExportType::class);

        $form->handleRequest($request);

        if (!($form->isSubmitted() && $form->isValid())) {
            return $this->redirectToReferer($this->generateUrl('ekyna_admin_dashboard'));
        }

        $date = $form->get('date')->getData();

        $path = $this->get('ekyna_commerce.accounting.exporter')->export(new \DateTime($date));

        clearstatcache(true, $path);

        $stream = new Stream($path);
        $response = new BinaryFileResponse($stream);
        $response->headers->set('Content-Type', 'text/csv');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'accounting_' . $date . '.csv'
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
