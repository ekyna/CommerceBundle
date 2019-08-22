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
     * @var TicketRenderer
     */
    private $renderer;

    /**
     * @var array
     */
    private $config;


    /**
     * Constructor.
     *
     * @param TicketRenderer $renderer
     * @param array          $config
     */
    public function __construct(TicketRenderer $renderer, array $config = [])
    {
        $this->renderer = $renderer;
        $this->config = array_replace([
            'enabled' => true,
        ], $config);
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'support_enabled',
                [$this, 'isSupportEnabled']
            ),
            new TwigFunction(
                'support_ticket',
                [$this->renderer, 'renderTicket'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'support_tickets',
                [$this->renderer, 'renderTickets'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Returns whether support is enabled.
     *
     * @return bool
     */
    public function isSupportEnabled()
    {
        return $this->config['enabled'];
    }
}
