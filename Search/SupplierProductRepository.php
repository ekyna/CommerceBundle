<?php

namespace Ekyna\Bundle\CommerceBundle\Search;

use Ekyna\Bundle\AdminBundle\Search\SearchRepositoryInterface;
use Elastica\Query;
use FOS\ElasticaBundle\Repository;

/**
 * Class SupplierProductRepository
 * @package Ekyna\Bundle\CommerceBundle\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductRepository extends Repository implements SearchRepositoryInterface
{
    /**
     * Search products.
     *
     * @param string  $expression
     * @param integer $limit
     *
     * @return \Ekyna\Component\Commerce\Product\Model\ProductInterface[]
     */
    public function defaultSearch($expression, $limit = 10)
    {
        if (0 == strlen($expression)) {
            $query = new Query\MatchAll();
        } else {
            $query = new Query\MultiMatch();
            $query
                ->setQuery($expression)
                ->setFields([
                    'designation',
                    'reference',
                ]);
        }

        return $this->find($query, $limit);
    }
}
