<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Search;

use Ekyna\Component\Resource\Search\Elastica\ResourceRepository;
use Ekyna\Component\Resource\Search\Request;
use Ekyna\Component\Resource\Search\Result;

/**
 * Class SupplierOrderRepository
 * @package Ekyna\Bundle\CommerceBundle\Service\Search
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderRepository extends ResourceRepository
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
            ->setIcon('fa fa-list')
            ->setRoute('ekyna_commerce_supplier_order_admin_show')
            ->setParameters(['supplierOrderId' => $source['id']]);
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultFields(): array
    {
        return [
            'number^2',
            'number.analyzed',
            'supplier.name',
            'supplier.name.analyzed',
            'carrier.name',
            'carrier.name.analyzed',
            'designation',
            'designation.analyzed',
        ];
    }
}
