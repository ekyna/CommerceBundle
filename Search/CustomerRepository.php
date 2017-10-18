<?php

namespace Ekyna\Bundle\CommerceBundle\Search;

use Ekyna\Component\Resource\Search\Elastica\ResourceRepository;
use Elastica\Query;
use Elastica\Filter;

/**
 * Class CustomerRepository
 * @package Ekyna\Bundle\CommerceBundle\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerRepository extends ResourceRepository
{
    /**
     * Search available parents.
     *
     * @param string $expression
     * @param int    $limit
     *
     * @return \Ekyna\Bundle\ProductBundle\Model\ProductInterface[]
     */
    public function searchAvailableParents($expression, $limit = 10)
    {
        $filteredQuery = new Query\Filtered();

        $matchQuery = new Query\MultiMatch();
        $matchQuery->setQuery($expression)->setFields($this->getDefaultMatchFields());
        $filteredQuery->setQuery($matchQuery);

        $boolFilter = new Filter\BoolFilter();
        $boolFilter->addMustNot(new Filter\Exists('parent'));
        $boolFilter->addMust(new Filter\Exists('company'));
        $boolFilter->addMust(new Filter\Term(['vatValid' => true]));

        $filteredQuery->setFilter($boolFilter);

        return $this->find($filteredQuery, $limit);
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultMatchFields()
    {
        return [
            'email',
            'first_name',
            'last_name',
            'company',
        ];
    }
}
