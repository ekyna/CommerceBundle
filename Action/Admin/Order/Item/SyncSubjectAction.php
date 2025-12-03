<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Order\Item;

use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item\SyncSubjectAction as BaseAction;
use Ekyna\Bundle\CommerceBundle\Service\SaleItemHelper;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Stock\Prioritizer\OrderPrioritizeCheckerInterface;
use Ekyna\Component\Commerce\Stock\Prioritizer\OrderPrioritizerInterface;

/**
 * Class SyncSubjectAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Order\Item
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SyncSubjectAction extends BaseAction
{
    public function __construct(
        private readonly SaleItemHelper                  $saleItemHelper,
        private readonly OrderPrioritizeCheckerInterface $prioritizeChecker,
        private readonly OrderPrioritizerInterface       $stockPrioritizer,
    ) {
        parent::__construct($this->saleItemHelper);
    }

    protected function postSync(SaleItemInterface $item): void
    {
        if (!$item instanceof OrderItemInterface) {
            throw new UnexpectedTypeException($item, OrderItemInterface::class);
        }

        if (!$this->prioritizeChecker->checkItem($item)) {
            return;
        }

        $changed = $this
            ->stockPrioritizer
            ->prioritizeItem($item);

        if ($changed) {
            $this->getManager($item)->flush();
        }
    }

    public static function configureAction(): array
    {
        return array_replace(parent::configureAction(), [
            'name' => 'commerce_order_item_sync_subject',
        ]);
    }
}
