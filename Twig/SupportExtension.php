<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Support\TicketRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class SupportExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupportExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'support_ticket',
                [TicketRenderer::class, 'renderTicket'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'support_tickets',
                [TicketRenderer::class, 'renderTickets'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
