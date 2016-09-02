<?php

namespace Ekyna\Bundle\CommerceBundle\Search;

use Ekyna\Bundle\AdminBundle\Search\SearchRepositoryInterface;
use Elastica\Query;
use FOS\ElasticaBundle\Repository;

/**
 * Class CustomerRepository
 * @package Ekyna\Bundle\CommerceBundle\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerRepository extends Repository implements SearchRepositoryInterface
{
    /**
     * Search users.
     *
     * @param string  $expression
     * @param integer $limit
     *
     * @return \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface[]
     */
    public function defaultSearch($expression, $limit = 10)
    {
        if (0 == strlen($expression)) {
            $query = new Query\MatchAll();
        } else {
            $query = new Query\MultiMatch();
            $query
                ->setQuery($expression)
                ->setFields(array('email', 'first_name', 'last_name', 'company'));
        }

        return $this->find($query, $limit);
    }
}
