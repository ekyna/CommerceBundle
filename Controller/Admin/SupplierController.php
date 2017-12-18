<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;

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
}
