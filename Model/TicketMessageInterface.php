<?php

declare(strict_types=1);

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
     * Returns the admin user.
     */
    public function getAdmin(): ?UserInterface;

    /**
     * Sets the admin user.
     */
    public function setAdmin(?UserInterface $admin): TicketMessageInterface;
}
