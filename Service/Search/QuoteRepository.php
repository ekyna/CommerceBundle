<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Search;

use Ekyna\Component\Resource\Search\Request;
use Ekyna\Component\Resource\Search\Result;

/**
 * Class QuoteRepository
 * @package Ekyna\Bundle\CommerceBundle\Service\Search
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteRepository extends SaleRepository
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
            ->setRoute('admin_ekyna_commerce_quote_read') // TODO Use resource/action
            ->setParameters(['quoteId' => $source['id']]);
    }
}
