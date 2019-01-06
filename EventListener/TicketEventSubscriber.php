<?php

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Ekyna\Bundle\CommerceBundle\Service\Common\InChargeResolver;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\TicketEventSubscriber as BaseSubscriber;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Quote\Repository\QuoteRepositoryInterface;
use Ekyna\Component\Commerce\Support\Event\TicketEvents;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class TicketEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method \Ekyna\Bundle\CommerceBundle\Model\TicketInterface getTicketFromEvent(ResourceEventInterface $event)
 */
class TicketEventSubscriber extends BaseSubscriber
{
    /**
     * @var InChargeResolver
     */
    protected $inChargeResolver;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var QuoteRepositoryInterface
     */
    protected $quoteRepository;


    /**
     * Sets the 'in charge' resolver.
     *
     * @param InChargeResolver $resolver
     */
    public function setInChargeResolver(InChargeResolver $resolver)
    {
        $this->inChargeResolver = $resolver;
    }

    /**
     * Sets the request stack.
     *
     * @param RequestStack $stack
     */
    public function setRequestStack(RequestStack $stack)
    {
        $this->requestStack = $stack;
    }

    /**
     * Sets the order repository.
     *
     * @param OrderRepositoryInterface $repository
     */
    public function setOrderRepository(OrderRepositoryInterface $repository)
    {
        $this->orderRepository = $repository;
    }

    /**
     * Sets the quote repository.
     *
     * @param QuoteRepositoryInterface $repository
     */
    public function setQuoteRepository(QuoteRepositoryInterface $repository)
    {
        $this->quoteRepository = $repository;
    }


    /**
     * Ticket initialize event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInitialize(ResourceEventInterface $event)
    {
        $ticket = $this->getTicketFromEvent($event);

        $this->inChargeResolver->update($ticket);

        $request = $this->requestStack->getCurrentRequest();

        if ($number = $request->query->get('order')) {
            $this->setTicketOrder($ticket, $number);
        } elseif ($number = $request->query->get('quote')) {
            $this->setTicketQuote($ticket, $number);
        }
    }

    /**
     * @inheritdoc
     *
     * @param \Ekyna\Bundle\CommerceBundle\Model\TicketInterface $ticket
     */
    protected function handleInsert(TicketInterface $ticket)
    {
        $changed = parent::handleInsert($ticket);

        $changed |= $this->inChargeResolver->update($ticket);

        return $changed;
    }

    /**
     * @inheritdoc
     *
     * @param \Ekyna\Bundle\CommerceBundle\Model\TicketInterface $ticket
     */
    protected function handleUpdate(TicketInterface $ticket)
    {
        $changed = parent::handleUpdate($ticket);

        $changed |= $this->inChargeResolver->update($ticket);

        return $changed;
    }

    /**
     * Sets the ticket order.
     *
     * @param TicketInterface $ticket
     * @param string          $number
     */
    private function setTicketOrder(TicketInterface $ticket, $number)
    {
        $order = $this->orderRepository->findOneByNumber($number);

        if (null === $order) {
            return;
        }

        /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
        if (null === $customer = $order->getCustomer()) {
            return;
        }

        if (null === $customer->getUser()) {
            return;
        }

        $ticket->addOrder($order);
        $ticket->setCustomer($customer);
    }

    /**
     * Sets the ticket quote.
     *
     * @param TicketInterface $ticket
     * @param string          $number
     */
    private function setTicketQuote(TicketInterface $ticket, $number)
    {
        $quote = $this->quoteRepository->findOneByNumber($number);

        if (null === $quote) {
            return;
        }

        /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
        if (null === $customer = $quote->getCustomer()) {
            return;
        }

        if (null === $customer->getUser()) {
            return;
        }

        $ticket->addQuote($quote);
        $ticket->setCustomer($customer);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return array_replace(parent::getSubscribedEvents(), [
            TicketEvents::INITIALIZE => ['onInitialize', 0],
        ]);
    }
}
