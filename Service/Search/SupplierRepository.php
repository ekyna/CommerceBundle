<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Search;

use Ekyna\Component\Resource\Search\Elastica\ResourceRepository;
use Ekyna\Component\Resource\Search\Request;
use Ekyna\Component\Resource\Search\Result;

/**
 * Class SupplierRepository
 * @package Ekyna\Bundle\CommerceBundle\Service\Search
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierRepository extends ResourceRepository
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
            ->setIcon('fa fa-truck')
            ->setRoute('ekyna_commerce_supplier_admin_show')
            ->setParameters(['supplierId' => $source['id']]);
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultFields(): array
    {
        return [
            'name^2',
            'name.analyzed',
            'designation',
            'designation.analyzed',
        ];
    }
}
