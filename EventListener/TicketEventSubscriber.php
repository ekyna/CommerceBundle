<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Service\Common\InChargeResolver;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\TicketEventSubscriber as BaseSubscriber;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class TicketEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method \Ekyna\Bundle\CommerceBundle\Model\TicketInterface getTicketFromEvent(ResourceEventInterface $event)
 */
class TicketEventSubscriber extends BaseSubscriber
{
    protected InChargeResolver $inChargeResolver;

    public function setInChargeResolver(InChargeResolver $resolver): void
    {
        $this->inChargeResolver = $resolver;
    }

    /**
     * @param \Ekyna\Bundle\CommerceBundle\Model\TicketInterface $ticket
     */
    protected function handleInsert(TicketInterface $ticket): bool
    {
        $changed = parent::handleInsert($ticket);

        return $this->inChargeResolver->update($ticket) || $changed;
    }

    /**
     * @param \Ekyna\Bundle\CommerceBundle\Model\TicketInterface $ticket
     */
    protected function handleUpdate(TicketInterface $ticket): bool
    {
        $changed = parent::handleUpdate($ticket);

        return $this->inChargeResolver->update($ticket) || $changed;
    }
}
