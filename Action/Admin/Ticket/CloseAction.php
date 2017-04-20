<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Ticket;

use Ekyna\Component\Commerce\Support\Model\TicketStates;
use Ekyna\Component\Resource\Action\Permission;

/**
 * Class CloseAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Ticket
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CloseAction extends AbstractAction
{
    protected static string $action = 'close';
    protected static string $state  = TicketStates::STATE_CLOSED;

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_ticket_close',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_close',
                'path'     => '/close',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'ticket.button.close',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'warning',
                'icon'         => 'cancel',
            ],
            'options'    => [
                'serialization' => ['groups' => ['Default'], 'admin' => true],
            ],
        ];
    }
}
