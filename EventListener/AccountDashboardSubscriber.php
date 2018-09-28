<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\UserBundle\Event\DashboardEvent;
use Ekyna\Bundle\UserBundle\Model\DashboardWidget;
use Ekyna\Bundle\UserBundle\Model\DashboardWidgetButton;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Ekyna\Component\Commerce\Invoice\Repository\InvoiceRepositoryInterface;
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
     * @var CustomerProviderInterface
     */
    private $customerProvider;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;


    /**
     * Constructor.
     *
     * @param CustomerProviderInterface  $customerProvider
     * @param QuoteRepositoryInterface   $quoteRepository
     * @param OrderRepositoryInterface   $orderRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(
        CustomerProviderInterface $customerProvider,
        QuoteRepositoryInterface $quoteRepository,
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->customerProvider = $customerProvider;
        $this->quoteRepository = $quoteRepository;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Account dashboard event handler.
     *
     * @param DashboardEvent $event
     */
    public function onDashboard(DashboardEvent $event)
    {
        $customer = $this->customerProvider->getCustomer();

        if (null !== $customer) {
            if (null !== $customer->getPaymentTerm()) {
                $widget = new DashboardWidget(
                    'ekyna_commerce.account.state.title',
                    'EkynaCommerceBundle:Account/Dashboard:state.html.twig',
                    'default'
                );
                $widget
                    ->setParameters([
                        'customer' => $customer,
                    ])
                    ->setPriority(1000);

                $event->addWidget($widget);
            }

            // Quotes widget
            if (!empty($quotes = $this->getQuotes($customer))) {
                $widget = new DashboardWidget(
                    'ekyna_commerce.account.quote.latest',
                    'EkynaCommerceBundle:Account/Quote:_list.html.twig',
                    'default'
                );
                $widget
                    ->setParameters([
                        'customer' => $customer,
                        'quotes'   => $quotes,
                    ])
                    ->setPriority(1000)
                    ->addButton(new DashboardWidgetButton(
                        'ekyna_commerce.account.quote.all',
                        'ekyna_commerce_account_quote_index',
                        [],
                        'primary'
                    ));

                $event->addWidget($widget);
            }

            // Orders widget
            if (!empty($orders = $this->getOrders($customer))) {
                $widget = new DashboardWidget(
                    'ekyna_commerce.account.order.latest',
                    'EkynaCommerceBundle:Account/Order:_list.html.twig',
                    'default'
                );
                $widget
                    ->setParameters([
                        'customer' => $customer,
                        'orders'   => $orders,
                    ])
                    ->setPriority(900)
                    ->addButton(new DashboardWidgetButton(
                        'ekyna_commerce.account.order.all',
                        'ekyna_commerce_account_order_index',
                        [],
                        'primary'
                    ));

                $event->addWidget($widget);
            }

            // Invoices widget
            if (!$customer->hasParent() && !empty($invoices = $this->getInvoices($customer))) {
                $widget = new DashboardWidget(
                    'ekyna_commerce.account.invoice.latest',
                    'EkynaCommerceBundle:Account/Invoice:_list.html.twig',
                    'default'
                );
                $widget
                    ->setParameters([
                        'invoices' => $invoices,
                    ])
                    ->setPriority(800)
                    ->addButton(new DashboardWidgetButton(
                        'ekyna_commerce.account.invoice.all',
                        'ekyna_commerce_account_invoice_index',
                        [],
                        'primary'
                    ));

                $event->addWidget($widget);
            }
        }
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
     * Returns the customer's new, pending or accepted orders.
     *
     * @param CustomerInterface $customer
     *
     * @return array|\Ekyna\Component\Commerce\Common\Model\SaleInterface[]
     */
    private function getOrders(CustomerInterface $customer)
    {
        $states = [OrderStates::STATE_PENDING, OrderStates::STATE_ACCEPTED];

        return $this->orderRepository->findByCustomer($customer, $states);
    }

    /**
     * Returns the customer's invoices.
     *
     * @param CustomerInterface $customer
     *
     * @return array|\Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface[]
     */
    private function getInvoices(CustomerInterface $customer)
    {
        return $this->invoiceRepository->findByCustomer($customer, 10);
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
