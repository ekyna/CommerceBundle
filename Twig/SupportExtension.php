<?php

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
    /**
     * @inheritdoc
     */
    public function getFunctions()
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
