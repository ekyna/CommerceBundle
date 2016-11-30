<?php

namespace Ekyna\Bundle\CommerceBundle\Search;

use Ekyna\Component\Resource\Search\Elastica\ResourceRepository;

/**
 * Class SupplierProductRepository
 * @package Ekyna\Bundle\CommerceBundle\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductRepository extends ResourceRepository
{
    /**
     * @inheritdoc
     */
    protected function getDefaultMatchFields()
    {
        return [
            'designation',
            'reference',
        ];
    }
}
