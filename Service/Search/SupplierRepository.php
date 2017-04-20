<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Search;

use Ekyna\Component\Resource\Bridge\Symfony\Elastica\SearchRepository;
use Ekyna\Component\Resource\Search\Request;
use Ekyna\Component\Resource\Search\Result;

/**
 * Class SupplierRepository
 * @package Ekyna\Bundle\CommerceBundle\Service\Search
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierRepository extends SearchRepository
{
    /**
     * @inheritDoc
     */
    protected function createResult($source, Request $request): ?Result
    {
        if (!$result = parent::createResult($source, $request)) {
            return null;
        }

        return $result
            ->setIcon('fa fa-truck')
            ->setRoute('admin_ekyna_commerce_supplier_read') // TODO Use resource/action
            ->setParameters(['supplierId' => $source['id']]);
    }

    protected function getDefaultFields(): array
    {
        return [
            'name^2',
            'name.analyzed',
            'designation',
            'designation.analyzed',
        ];
    }
}
