<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Search;

use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Search\Elastica\ResourceRepository;
use Ekyna\Component\Resource\Search\Request;
use Ekyna\Component\Resource\Search\Result;

/**
 * Class SaleRepository
 * @package Ekyna\Bundle\CommerceBundle\Service\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class SaleRepository extends ResourceRepository
{
    /**
     * @inheritDoc
     */
    protected function createResult($source, Request $request): ?Result
    {
        if (!$request->isPrivate()) {
            return null;
        }

        if (!is_array($source)) {
            throw new UnexpectedTypeException($source, 'array');
        }

        if (empty($source['company'])) {
            $title = sprintf(
                '[%s] %s %s',
                $source['number'],
                $source['first_name'],
                $source['last_name']
            );
        } else {
            $title = sprintf(
                '[%s] %s %s %s',
                $source['number'],
                $source['company'],
                $source['first_name'],
                $source['last_name']
            );
        }

        if (!empty($source['title'])) {
            $title .= " ({$source['title']})";
        }

        $result = new Result();

        return $result
            ->setTitle($title)
            ->setIcon('fa fa-shopping-cart');
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
            'voucher_number',
            'voucher_number.analyzed',
            'title',
            'title.analyzed',
            'email',
            'email.analyzed',
        ];
    }
}
