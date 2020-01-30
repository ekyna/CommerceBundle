<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Search;

use Ekyna\Component\Resource\Search\Elastica\ResourceRepository;
use Ekyna\Component\Resource\Search\Request;
use Ekyna\Component\Resource\Search\Result;
use Elastica\Query;

/**
 * Class CustomerRepository
 * @package Ekyna\Bundle\CommerceBundle\Service\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerRepository extends ResourceRepository
{
    /**
     * @inheritDoc
     */
    protected function createQuery(Request $request): Query\AbstractQuery
    {
        $query = parent::createQuery($request);

        if (empty($parent = $request->getParameter('parent'))) {
            return $query;
        }

        $bool = new Query\BoolQuery();
        $bool
            ->addMust($query)
            ->addMustNot(new Query\Exists('parent'))
            ->addMust(new Query\Exists('company'))
            ->addMust(new Query\Term(['vatValid' => true]));

        return $bool;
    }

    /**
     * @inheritDoc
     */
    protected function createResult($source, Request $request): ?Result
    {
        if (!$request->isPrivate()) {
            return null;
        }

        $result = new Result();

        return $result
            ->setTitle($source['text'])
            ->setIcon('fa fa-user')
            ->setRoute('ekyna_commerce_customer_admin_show')
            ->setParameters(['customerId' => $source['id']]);
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultFields(): array
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
            'company_number',
            'company_number.analyzed',
            'email',
            'email.analyzed',
        ];
    }
}
