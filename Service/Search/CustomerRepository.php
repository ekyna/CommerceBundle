<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Search;

use Ekyna\Component\Resource\Search\Elastica\ResourceRepository;
use Elastica\Query;

/**
 * Class CustomerRepository
 * @package Ekyna\Bundle\CommerceBundle\Service\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerRepository extends ResourceRepository
{
    /**
     * Creates the search query.
     *
     * @param string $expression
     * @param bool   $parent
     *
     * @return Query
     */
    public function createSearchQuery(string $expression, bool $parent = false): Query
    {
        $match = new Query\MultiMatch();
        $match
            ->setQuery($expression)
            ->setType(Query\MultiMatch::TYPE_CROSS_FIELDS)
            ->setFields($this->getDefaultMatchFields());

        if (!$parent) {
            return Query::create($match);
        }

        $bool = new Query\BoolQuery();
        $bool
            ->addMust($match)
            ->addMustNot(new Query\Exists('parent'))
            ->addMust(new Query\Exists('company'))
            ->addMust(new Query\Term(['vatValid' => true]));

        return Query::create($bool);
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultMatchFields(): array
    {
        return [
            'company^3',
            'company.analyzed',
            'last_name^2',
            'last_name.analyzed',
            'first_name^2',
            'first_name.analyzed',
            'number',
            'number.analyzed',
            'email',
            'email.analyzed',
        ];
    }
}
