<?php

namespace Ekyna\Bundle\CommerceBundle\Dashboard;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\AbstractWidgetType;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use Ekyna\Bundle\CommerceBundle\Table\Type\TicketType;
use Ekyna\Component\Commerce\Support\Repository\TicketRepositoryInterface;
use Ekyna\Component\Table\Extension\Core\Source\ArraySource;
use Ekyna\Component\Table\FactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Class SupportWidget
 * @package Ekyna\Bundle\CommerceBundle\Dashboard
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupportWidget extends AbstractWidgetType
{
    /**
     * @var TicketRepositoryInterface
     */
    protected $ticketRepository;

    /**
     * @var FactoryInterface
     */
    protected $tableFactory;

    /**
     * @var RequestStack
     */
    protected $requestStack;


    /**
     * Constructor.
     *
     * @param TicketRepositoryInterface $repository
     * @param FactoryInterface          $tableFactory
     * @param RequestStack              $requestStack
     */
    public function __construct(
        TicketRepositoryInterface $repository,
        FactoryInterface $tableFactory,
        RequestStack $requestStack
    ) {
        $this->ticketRepository = $repository;
        $this->tableFactory = $tableFactory;
        $this->requestStack = $requestStack;
    }

    /**
     * @inheritDoc
     */
    public function render(WidgetInterface $widget, Environment $twig)
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

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'title'    => 'ekyna_commerce.ticket.label.plural',
            'position' => 1000,
            'col_md'   => 12,
            'css_path' => 'bundles/ekynacommerce/css/support.css',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'commerce_support';
    }
}
