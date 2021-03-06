<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Search;

use Ekyna\Component\Resource\Search\Elastica\ResourceRepository;
use Ekyna\Component\Resource\Search\Request;
use Ekyna\Component\Resource\Search\Result;

/**
 * Class SupplierProductRepository
 * @package Ekyna\Bundle\CommerceBundle\Service\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductRepository extends ResourceRepository
{
    /**
     * @inheritDoc
     */
    protected function createResult($source, Request $request): ?Result
    {
        if (!$result = parent::createResult($source, $request)) {
            return null;
        }

        return $result
            ->setIcon('fa fa-cubes')
            ->setRoute('ekyna_commerce_supplier_product_admin_show')
            ->setParameters([
                'supplierId'        => $source['supplier']['id'],
                'supplierProductId' => $source['id'],
            ]);
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultFields(): array
    {
        return [
            'reference^2',
            'reference.analyzed',
            'designation',
            'designation.analyzed',
        ];
    }
}
