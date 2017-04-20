<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\CommerceBundle\Model\TicketMessageInterface;
use Ekyna\Component\Commerce\Support\Entity\TicketMessage as BaseTicket;

/**
 * Class TicketMessage
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketMessage extends BaseTicket implements TicketMessageInterface
{
    protected ?UserInterface $admin = null;

    public function getAdmin(): ?UserInterface
    {
        return $this->admin;
    }

    public function setAdmin(UserInterface $admin = null): TicketMessageInterface
    {
        $this->admin = $admin;

        return $this;
    }

    public function isCustomer(): bool
    {
        return null === $this->admin;
    }
}
