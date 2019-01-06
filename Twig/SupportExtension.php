<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\Support\TicketRenderer;

/**
 * Class SupportExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupportExtension extends \Twig_Extension
{
    /**
     * @var TicketRenderer
     */
    private $renderer;


    /**
     * Constructor.
     *
     * @param TicketRenderer $renderer
     */
    public function __construct(TicketRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'render_ticket',
                [$this->renderer, 'renderTicket'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'render_tickets',
                [$this->renderer, 'renderTickets'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
