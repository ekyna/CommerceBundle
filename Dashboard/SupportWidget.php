<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Dashboard;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\AbstractWidgetType;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use Ekyna\Bundle\CommerceBundle\Table\Type\TicketType;
use Ekyna\Component\Commerce\Support\Repository\TicketRepositoryInterface;
use Ekyna\Component\Table\Extension\Core\Source\ArraySource;
use Ekyna\Component\Table\TableFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

use function Symfony\Component\Translation\t;

/**
 * Class SupportWidget
 * @package Ekyna\Bundle\CommerceBundle\Dashboard
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupportWidget extends AbstractWidgetType
{
    public const NAME = 'commerce_support';

    protected TicketRepositoryInterface $ticketRepository;
    protected TableFactoryInterface     $tableFactory;
    protected RequestStack              $requestStack;

    public function __construct(
        TicketRepositoryInterface $repository,
        TableFactoryInterface     $tableFactory,
        RequestStack              $requestStack
    ) {
        $this->ticketRepository = $repository;
        $this->tableFactory = $tableFactory;
        $this->requestStack = $requestStack;
    }

    public function render(WidgetInterface $widget, Environment $twig): string
    {
        $tickets = $this
            ->tableFactory
            ->createTable('tickets', TicketType::class, [
                'sortable'     => false,
                'filterable'   => false,
                'batchable'    => false,
                'exportable'   => false,
                'configurable' => false,
                'profileable'  => false,
                'source'       => new ArraySource($this->ticketRepository->findNotClosed()),
            ]);

        $tickets->handleRequest($this->requestStack->getCurrentRequest());

        return $twig->render('@EkynaCommerce/Admin/Dashboard/widget_support.html.twig', [
            'tickets' => $tickets->createView(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'title'    => t('ticket.label.plural', [], 'EkynaCommerce'),
            'position' => 1000,
            'col_md'   => 12,
            'css_path' => 'bundles/ekynacommerce/css/support.css',
        ]);
    }

    public static function getName(): string
    {
        return self::NAME;
    }
}
