<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Search;

use Ekyna\Component\Resource\Bridge\Symfony\Elastica\SearchRepository;
use Ekyna\Component\Resource\Search\Request;
use Ekyna\Component\Resource\Search\Result;

/**
 * Class SupplierProductRepository
 * @package Ekyna\Bundle\CommerceBundle\Service\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductRepository extends SearchRepository
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
            ->setRoute('admin_ekyna_commerce_supplier_product_read') // TODO Use resource/action
            ->setParameters([
                'supplierId'        => $source['supplier']['id'],
                'supplierProductId' => $source['id'],
            ]);
    }

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
