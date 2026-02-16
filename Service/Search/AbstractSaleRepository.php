<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Search;

use Ekyna\Component\Resource\Bridge\Symfony\Elastica\SearchRepository;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Search\Request;
use Ekyna\Component\Resource\Search\Result;

/**
 * Class AbstractSaleRepository
 * @package Ekyna\Bundle\CommerceBundle\Service\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleRepository extends SearchRepository
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
                $source['firstName'],
                $source['lastName']
            );
        } else {
            $title = sprintf(
                '[%s] %s %s %s',
                $source['number'],
                $source['company'],
                $source['firstName'],
                $source['lastName']
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
     * @inheritDoc
     */
    protected function getDefaultFields(): array
    {
        return [
            'company^3',
            'company.analyzed',
            'lastName^2',
            'lastName.analyzed',
            'firstName^2',
            'firstName.analyzed',
            'number',
            'number.analyzed',
            'voucherNumber',
            'voucherNumber.analyzed',
            'title',
            'title.analyzed',
            'email',
            'email.analyzed',
        ];
    }
}
