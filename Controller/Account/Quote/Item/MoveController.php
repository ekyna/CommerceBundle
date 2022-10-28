<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Item;

use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteViewHelper;
use Ekyna\Bundle\CommerceBundle\Service\SaleHelper;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MoveController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Item
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MoveController extends AbstractItemController
{
    public function __construct(
        protected readonly QuoteResourceHelper    $resourceHelper,
        protected readonly SaleHelper             $saleHelper,
        private readonly ResourceManagerInterface $quoteItemManager,
        private readonly QuoteViewHelper          $viewHelper,
    ) {
    }

    public function up(Request $request): Response
    {
        $quote = $this->findQuote($request);

        $item = $this->findItem($request, $quote);

        if (0 < $item->getPosition()) {
            $item->setPosition($item->getPosition() - 1);

            $this->quoteItemManager->update($item);
        }

        return $this->viewHelper->buildXhrSaleViewResponse($quote);
    }

    public function down(Request $request): Response
    {
        $quote = $this->findQuote($request);

        $item = $this->findItem($request, $quote);

        if (!$item->isLast()) {
            $item->setPosition($item->getPosition() + 1);

            $this->quoteItemManager->update($item);
        }

        return $this->viewHelper->buildXhrSaleViewResponse($quote);
    }
}
