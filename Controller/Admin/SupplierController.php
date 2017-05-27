<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CommerceBundle\Table\Type\SupplierProductType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;

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

        $source = new EntitySource($this->getParameter('ekyna_commerce.supplier_product.class'));
        $source->setQueryBuilderInitializer(function (QueryBuilder $qb, $alias) use ($supplier) {
            $qb
                ->andWhere($qb->expr()->eq($alias . '.supplier', ':supplier'))
                ->setParameter('supplier', $supplier);
        });

        $table = $this
            ->getTableFactory()
            ->createTable('products', SupplierProductType::class, [
                'source' => $source,
            ]);

        if (null !== $response = $table->handleRequest($context->getRequest())) {
            return $response;
        }

        $data['supplierProducts'] = $table->createView();

        return null;
    }
}
