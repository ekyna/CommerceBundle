<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Search;

use Ekyna\Component\Resource\Search\Elastica\ResourceRepository;

/**
 * Class SaleRepository
 * @package Ekyna\Bundle\CommerceBundle\Service\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleRepository extends ResourceRepository
{
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
