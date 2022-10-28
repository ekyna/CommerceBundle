<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Item;

use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteViewHelper;
use Ekyna\Bundle\CommerceBundle\Service\SaleHelper;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class DeleteController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Item
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DeleteController extends AbstractItemController
{
    public function __construct(
        protected readonly QuoteResourceHelper    $resourceHelper,
        protected readonly SaleHelper             $saleHelper,
        private readonly ResourceManagerInterface $quoteItemManager,
        private readonly QuoteViewHelper          $viewHelper,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $quote = $this->findQuote($request);
        $item = $this->findItem($request, $quote);

        if ($item->isImmutable()) {
            throw new NotFoundHttpException('Item is immutable.');
        }

        $this->quoteItemManager->delete($item);

        return $this->viewHelper->buildXhrSaleViewResponse($quote);
    }
}
