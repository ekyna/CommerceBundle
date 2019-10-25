<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface as BaseInterface;

/**
 * Interface TicketMessageInterface
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TicketMessageInterface extends BaseInterface
{
    /**
     * Returns the admin.
     *
     * @return UserInterface
     */
    public function getAdmin();

    /**
     * Sets the admin.
     *
     * @param UserInterface $admin
     *
     * @return $this|TicketMessageInterface
     */
    public function setAdmin(UserInterface $admin = null);
}
