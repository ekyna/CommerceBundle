<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SupplierController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierController extends ResourceController
{
    /**
     * @inheritdoc
     */
    protected function buildShowData(array &$data, Context $context)
    {
        $supplier = $context->getResource();

        $type = $this->get('ekyna_commerce.supplier_product.configuration')->getTableType();

        $table = $this
            ->getTableFactory()
            ->createTable('products', $type, [
                'supplier' => $supplier,
            ]);

        if (null !== $response = $table->handleRequest($context->getRequest())) {
            return $response;
        }

        $data['supplierProducts'] = $table->createView();

        return null;
    }

    /**
     * Sale summary action.
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
        /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierInterface $supplier */
        $supplier = $context->getResource();

        $this->isGranted('VIEW', $supplier);

        $response = new Response();
        $response->setVary(['Accept', 'Accept-Encoding']);
        $response->setLastModified($supplier->getUpdatedAt());

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
            $content = $this->renderView(
                '@EkynaCommerce/Admin/Supplier/summary.html.twig',
                $this->get('serializer')->normalize($supplier, 'json', ['groups' => ['Summary']])
            );
        } else {
            $content = $this->get('serializer')->serialize($supplier, 'json', ['groups' => ['Summary']]);
        }

        $response->setContent($content);

        return $response;
    }
}
