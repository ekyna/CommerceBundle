<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Model\InChargeSubjectInterface;
use Ekyna\Component\Commerce\Common\Event\SaleTransformEvent;

/**
 * Class InChargeSaleCopyListener
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InChargeSaleCopyListener
{
    public function onPostCopy(SaleTransformEvent $event): void
    {
        $source = $event->getSource();
        $target = $event->getTarget();

        if ($target instanceof InChargeSubjectInterface
            && $source instanceof InChargeSubjectInterface) {
            $target->setInCharge($source->getInCharge());
        }
    }
}
