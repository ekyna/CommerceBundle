<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Search;

use Ekyna\Component\Resource\Search\Request;
use Ekyna\Component\Resource\Search\Result;

/**
 * Class OrderRepository
 * @package Ekyna\Bundle\CommerceBundle\Service\Search
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderRepository extends SaleRepository
{
    /**
     * @inheritDoc
     */
    protected function createResult($source, Request $request): ?Result
    {
        if (!$result = parent::createResult($source, $request)) {
            return null;
        }

        if (!$request->isPrivate()) {
            return null;
        }

        return $result
            ->setRoute('ekyna_commerce_order_admin_show')
            ->setParameters(['orderId' => $source['id']]);
    }
}
