<?php

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Ekyna\Bundle\CommerceBundle\Model\InChargeSubjectTrait;
use Ekyna\Bundle\CommerceBundle\Model\TicketInterface;
use Ekyna\Component\Commerce\Support\Entity\Ticket as BaseTicket;

/**
 * Class Ticket
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Ticket extends BaseTicket implements TicketInterface
{
    use InChargeSubjectTrait;
}
