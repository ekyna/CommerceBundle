<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Service\Checker\SaleItemsChecker;
use Ekyna\Component\Commerce\Common\Event\SaleTransformEvent;

/**
 * Class SaleOperationListener
 * @package Ekyna\Bundle\ProductBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleOperationListener
{
    public function __construct(
        private readonly SaleItemsChecker $saleItemsChecker,
    ) {
    }

    public function onPreDuplicate(SaleTransformEvent $event): void
    {
        $target = $event->getTarget();

        $this->saleItemsChecker->check($target);
    }

    public function onPreTransform(SaleTransformEvent $event): void
    {
        $target = $event->getTarget();

        $this->saleItemsChecker->check($target);
    }
}
