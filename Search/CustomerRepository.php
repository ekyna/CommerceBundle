<?php

namespace Ekyna\Bundle\CommerceBundle\Search;

use Ekyna\Component\Resource\Search\Elastica\ResourceRepository;

/**
 * Class CustomerRepository
 * @package Ekyna\Bundle\CommerceBundle\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerRepository extends ResourceRepository
{
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
