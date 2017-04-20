<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Component\Commerce\Support\Model\TicketInterface as BaseInterface;

/**
 * Interface TicketInterface
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TicketInterface extends BaseInterface, InChargeSubjectInterface
{

}
