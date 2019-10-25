<?php

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
    /**
     * @var UserInterface
     */
    protected $admin;


    /**
     * @inheritdoc
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @inheritdoc
     */
    public function setAdmin(UserInterface $admin = null)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isCustomer()
    {
        return null === $this->admin;
    }
}
