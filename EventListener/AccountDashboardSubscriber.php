<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Repository\CustomerRepository;
use Ekyna\Bundle\UserBundle\Event\DashboardEvent;
use Ekyna\Bundle\UserBundle\Model\DashboardWidget;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteStates;
use Ekyna\Component\Commerce\Quote\Repository\QuoteRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AccountDashboardSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccountDashboardSubscriber implements EventSubscriberInterface
{
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;


    /**
     * Constructor.
     *
     * @param CustomerRepository       $customerRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param QuoteRepositoryInterface $quoteRepository
     */
    public function __construct(
        CustomerRepository $customerRepository,
        OrderRepositoryInterface $orderRepository,
        QuoteRepositoryInterface $quoteRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Account dashboard event handler.
     *
     * @param DashboardEvent $event
     */
    public function onDashboard(DashboardEvent $event)
    {
        $customer = $this->customerRepository->findOneByUser($event->getUser());

        if (null !== $customer) {
            // Orders widget
            if (!empty($orders = $this->getOrders($customer))) {
                $widget = new DashboardWidget(
                    'ekyna_commerce.account.order.title',
                    'EkynaCommerceBundle:Account/Order:_list.html.twig'
                );
                $widget
                    ->setParameters([
                        'customer' => $customer,
                        'orders'   => $orders,
                    ])
                    ->setPriority(1000);

                $event->addWidget($widget);
            }

            // Quotes widget
            if (!empty($quotes = $this->getQuotes($customer))) {
                $widget = new DashboardWidget(
                    'ekyna_commerce.account.quote.title',
                    'EkynaCommerceBundle:Account/Quote:_list.html.twig'
                );
                $widget
                    ->setParameters([
                        'customer' => $customer,
                        'quotes'   => $quotes,
                    ])
                    ->setPriority(900);

                $event->addWidget($widget);
            }
        }
    }

    /**
     * Returns the customer's new, pending or accepted orders.
     *
     * @param CustomerInterface $customer
     *
     * @return array|\Ekyna\Component\Commerce\Common\Model\SaleInterface[]
     */
    private function getOrders(CustomerInterface $customer)
    {
        $states = [OrderStates::STATE_NEW, OrderStates::STATE_PENDING, OrderStates::STATE_ACCEPTED];

        return $this->orderRepository->findByCustomer($customer, $states);
    }

    /**
     * Returns the customer's new, pending or accepted quotes.
     *
     * @param CustomerInterface $customer
     *
     * @return array|\Ekyna\Component\Commerce\Common\Model\SaleInterface[]
     */
    private function getQuotes(CustomerInterface $customer)
    {
        $states = [QuoteStates::STATE_NEW, QuoteStates::STATE_PENDING, QuoteStates::STATE_ACCEPTED];

        return $this->quoteRepository->findByCustomer($customer, $states);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            DashboardEvent::DASHBOARD => ['onDashboard', 0],
        ];
    }
}
