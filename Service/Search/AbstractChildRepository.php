<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Search;

use Ekyna\Component\Resource\Bridge\Symfony\Elastica\SearchRepository;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Search\Request;
use Ekyna\Component\Resource\Search\Result;

use function sprintf;

/**
 * Class AbstractChildRepository
 * @package Ekyna\Bundle\CommerceBundle\Service\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractChildRepository extends SearchRepository
{
    protected string $icon = 'fa fa-file';

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

        $result = new Result();

        return $result
            ->setTitle(sprintf('%s (%s)', $source['number'], $source['sale_number']))
            ->setIcon($this->icon)
            ->setRoute('admin_ekyna_commerce_order_read') // TODO Use resource/action
            ->setParameters(['orderId' => $source['sale_id']]);
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultFields(): array
    {
        return [
            'number',
            'number.analyzed',
        ];
    }
}
