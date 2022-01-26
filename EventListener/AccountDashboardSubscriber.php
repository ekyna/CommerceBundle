<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\UserBundle\Event\DashboardEvent;
use Ekyna\Bundle\UserBundle\Model\DashboardWidget;
use Ekyna\Bundle\UserBundle\Model\DashboardWidgetButton;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Ekyna\Component\Commerce\Invoice\Repository\InvoiceRepositoryInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteStates;
use Ekyna\Component\Commerce\Quote\Repository\QuoteRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function Symfony\Component\Translation\t;

/**
 * Class AccountDashboardSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccountDashboardSubscriber implements EventSubscriberInterface
{
    private CustomerProviderInterface  $customerProvider;
    private QuoteRepositoryInterface   $quoteRepository;
    private OrderRepositoryInterface   $orderRepository;
    private InvoiceRepositoryInterface $invoiceRepository;

    public function __construct(
        CustomerProviderInterface  $customerProvider,
        QuoteRepositoryInterface   $quoteRepository,
        OrderRepositoryInterface   $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->customerProvider = $customerProvider;
        $this->quoteRepository = $quoteRepository;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    public function onDashboard(DashboardEvent $event): void
    {
        /** @var CustomerInterface $customer */
        $customer = $this->customerProvider->getCustomer();

        if (!$customer) {
            return;
        }

        $this->addWelcomeWidget($event, $customer);
        $this->addStateWidget($event, $customer);
        $this->addQuotesWidget($event, $customer);
        $this->addOrdersWidget($event, $customer);
        $this->addInvoicesWidget($event, $customer);
    }

    private function addWelcomeWidget(DashboardEvent $event, CustomerInterface $customer): void
    {
        // Welcome
        $widget = DashboardWidget::create('@EkynaCommerce/Account/Dashboard/welcome.html.twig')
            ->setParameters([
                'customer' => $customer,
            ])
            ->setPriority(1000);

        $event->addWidget($widget);
    }

    private function addStateWidget(DashboardEvent $event, CustomerInterface $customer): void
    {
        if (!$customer->isBusiness() || !$customer->getPaymentTerm()) {
            return;
        }

        $widget = DashboardWidget::create('@EkynaCommerce/Account/Dashboard/state.html.twig')
            ->setTitle(t('account.state.title', [], 'EkynaCommerce'))
            ->setParameters([
                'customer' => $customer,
            ])
            ->setPriority(900);

        $event->addWidget($widget);
    }

    private function addQuotesWidget(DashboardEvent $event, CustomerInterface $customer): void
    {
        $states = [QuoteStates::STATE_NEW, QuoteStates::STATE_PENDING, QuoteStates::STATE_ACCEPTED];

        $quotes = $this->quoteRepository->findByCustomer($customer, $states);

        if (empty($quotes)) {
            return;
        }

        $widget = DashboardWidget::create('@EkynaCommerce/Account/Quote/_list.html.twig')
            ->setTitle(t('account.quote.latest', [], 'EkynaCommerce'))
            ->setParameters([
                'customer' => $customer,
                'quotes'   => $quotes,
            ])
            ->setPriority(800)
            ->addButton(new DashboardWidgetButton(
                t('account.quote.all', [], 'EkynaCommerce'),
                'ekyna_commerce_account_quote_index',
                [],
                'primary'
            ));

        $event->addWidget($widget);
    }

    private function addOrdersWidget(DashboardEvent $event, CustomerInterface $customer): void
    {
        $states = [OrderStates::STATE_PENDING, OrderStates::STATE_ACCEPTED];

        $orders = $this->orderRepository->findByCustomer($customer, $states);

        if (empty($orders)) {
            return;
        }

        $widget = DashboardWidget::create('@EkynaCommerce/Account/Order/_list.html.twig')
            ->setTitle(t('account.order.latest', [], 'EkynaCommerce'))
            ->setParameters([
                'customer' => $customer,
                'orders'   => $orders,
            ])
            ->setPriority(700)
            ->addButton(new DashboardWidgetButton(
                t('account.order.all', [], 'EkynaCommerce'),
                'ekyna_commerce_account_order_index',
                [],
                'primary'
            ));

        $event->addWidget($widget);
    }

    private function addInvoicesWidget(DashboardEvent $event, CustomerInterface $customer): void
    {
        if ($customer->hasParent()) {
            return;
        }

        $invoices = $this->invoiceRepository->findByCustomer($customer, 10);

        if (empty($invoices)) {
            return;
        }

        $widget = DashboardWidget::create('@EkynaCommerce/Account/Invoice/_list.html.twig')
            ->setTitle(t('account.invoice.latest', [], 'EkynaCommerce'))
            ->setParameters([
                'customer' => $customer,
                'invoices' => $invoices,
            ])
            ->setPriority(600)
            ->addButton(new DashboardWidgetButton(
                t('account.invoice.all', [], 'EkynaCommerce'),
                'ekyna_commerce_account_invoice_index',
                [],
                'primary'
            ));

        $event->addWidget($widget);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DashboardEvent::class => ['onDashboard', 0],
        ];
    }
}
