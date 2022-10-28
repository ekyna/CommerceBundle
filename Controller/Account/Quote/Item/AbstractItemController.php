<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Item;

use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\SaleHelper;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AbstractItemController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Item
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractItemController
{
    protected readonly QuoteResourceHelper $resourceHelper;
    protected readonly SaleHelper          $saleHelper;

    /**
     * Finds the quote.
     */
    protected function findQuote(Request $request): QuoteInterface
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not yet implemented');
        }

        $customer = $this->resourceHelper->getCustomer();

        $quote = $this
            ->resourceHelper
            ->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        if (null === $quote) {
            throw new NotFoundHttpException('Quote nof found');
        }

        if (!$quote->isEditable()) {
            throw new AccessDeniedHttpException('Quote is not editable.');
        }

        return $quote;
    }

    /**
     * Finds the quote item.
     */
    protected function findItem(Request $request, QuoteInterface $quote): QuoteItemInterface
    {
        $itemId = $request->attributes->getInt('id');
        if (0 >= $itemId) {
            throw new NotFoundHttpException('Unexpected item identifier.');
        }

        /** @var QuoteItemInterface $item */
        if (null === $item = $this->saleHelper->findItemById($quote, $itemId)) {
            throw new NotFoundHttpException('Item not found.');
        }

        return $item;
    }
}
