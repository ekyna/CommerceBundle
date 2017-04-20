<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Ticket;

use Ekyna\Component\Commerce\Support\Model\TicketStates;
use Ekyna\Component\Resource\Action\Permission;

/**
 * Class OpenAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Ticket
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OpenAction extends AbstractAction
{
    protected static string $action = 'open';
    protected static string $state  = TicketStates::STATE_NEW;

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_ticket_open',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_open',
                'path'     => '/open',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'ticket.button.open',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'warning',
                'icon'         => 'ok',
            ],
            'options'    => [
                'serialization' => ['groups' => ['Default'], 'admin' => true],
            ],
        ];
    }
}
