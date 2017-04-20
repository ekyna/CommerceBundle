<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Search;

use Ekyna\Component\Resource\Bridge\Symfony\Elastica\SearchRepository;
use Ekyna\Component\Resource\Search\Request;
use Ekyna\Component\Resource\Search\Result;

/**
 * Class SupplierOrderRepository
 * @package Ekyna\Bundle\CommerceBundle\Service\Search
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderRepository extends SearchRepository
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
            ->setRoute('admin_ekyna_commerce_supplier_order_read') // TODO Use resource/action
            ->setParameters(['supplierOrderId' => $source['id']]);
    }

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
