<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\CommerceBundle\Model\InChargeSubjectInterface;
use Ekyna\Component\Commerce\Common\Event\SaleTransformEvent;
use Ekyna\Component\User\Service\UserProviderInterface;

/**
 * Class InChargeSaleCopyListener
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InChargeSaleCopyListener
{
    public function __construct(
        private readonly UserProviderInterface $userProvider,
    ) {
    }

    public function onPostCopy(SaleTransformEvent $event): void
    {
        $source = $event->getSource();
        $target = $event->getTarget();

        if (!$target instanceof InChargeSubjectInterface) {
            return;
        }

        if ($event->isDuplicate() || !$source instanceof InChargeSubjectInterface) {
            $currentUser = $this->userProvider->getUser();

            if (!$currentUser instanceof UserInterface) {
                return;
            }

            $target->setInCharge($currentUser);

            return;
        }

        $target->setInCharge($source->getInCharge());
    }
}
