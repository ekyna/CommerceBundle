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
            new \Twig_SimpleFunction(
                'support_enabled',
                [$this, 'isSupportEnabled']
            ),
            new \Twig_SimpleFunction(
                'support_ticket',
                [$this->renderer, 'renderTicket'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
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
