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
     * (non-mapped)
     *
     * @var bool
     */
    protected $notify = true;


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
     * Returns whether to notify the customer or admin.
     *
     * @return bool
     */
    public function isNotify()
    {
        return $this->notify;
    }

    /**
     * Sets whether to notify the customer or admin.
     *
     * @param bool $notify
     *
     * @return TicketMessage
     */
    public function setNotify(bool $notify)
    {
        $this->notify = $notify;

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
