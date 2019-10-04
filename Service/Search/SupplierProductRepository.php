<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Search;

use Ekyna\Component\Resource\Search\Elastica\ResourceRepository;

/**
 * Class SupplierProductRepository
 * @package Ekyna\Bundle\CommerceBundle\Service\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductRepository extends ResourceRepository
{
    /**
     * @inheritdoc
     */
    protected function getDefaultMatchFields(): array
    {
        return [
            'reference^2',
            'reference.analyzed',
            'designation',
            'designation.analyzed',
        ];
    }
}
