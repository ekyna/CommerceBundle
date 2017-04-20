<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Factory;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Bundle\CommerceBundle\Model\TicketInterface;
use Ekyna\Bundle\CommerceBundle\Service\Common\InChargeResolver;
use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Factory\TicketFactory as BaseFactory;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Support\Factory\TicketMessageFactoryInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class TicketFactory
 * @package Ekyna\Bundle\CommerceBundle\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class TicketFactory extends BaseFactory
{
    private InChargeResolver           $inChargeResolver;
    private RequestStack               $requestStack;
    private RepositoryFactoryInterface $repositoryFactory;

    public function __construct(
        TicketMessageFactoryInterface $messageFactory,
        InChargeResolver              $inChargeResolver,
        RequestStack                  $requestStack,
        RepositoryFactoryInterface    $repositoryFactory
    ) {
        parent::__construct($messageFactory);

        $this->inChargeResolver = $inChargeResolver;
        $this->requestStack = $requestStack;
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * @return TicketInterface
     */
    public function create(): ResourceInterface
    {
        /** @var TicketInterface $ticket */
        $ticket = parent::create();

        $this->inChargeResolver->update($ticket);

        $request = $this->requestStack->getCurrentRequest();

        if ($number = $request->query->get('order')) {
            $this->setTicketOrder($ticket, $number);
        } elseif ($number = $request->query->get('quote')) {
            $this->setTicketQuote($ticket, $number);
        } elseif ($number = $request->query->get('customer')) {
            $this->setTicketCustomer($ticket, $number);
        }

        return $ticket;
    }

    private function setTicketOrder(TicketInterface $ticket, string $number): void
    {
        $order = $this
            ->repositoryFactory
            ->getRepository(OrderInterface::class)
            ->findOneByNumber($number);

        if (null === $order) {
            return;
        }

        $ticket->addOrder($order);

        if (null === $customer = $order->getCustomer()) {
            return;
        }

        $ticket->setCustomer($customer);
    }

    /**
     * Sets the ticket quote.
     *
     * @param TicketInterface $ticket
     * @param string          $number
     */
    private function setTicketQuote(TicketInterface $ticket, $number): void
    {
        $quote = $this
            ->repositoryFactory
            ->getRepository(QuoteInterface::class)
            ->findOneByNumber($number);

        if (null === $quote) {
            return;
        }

        $ticket->addQuote($quote);

        if (null === $customer = $quote->getCustomer()) {
            return;
        }

        $ticket->setCustomer($customer);
    }

    /**
     * Sets the ticket quote.
     *
     * @param TicketInterface $ticket
     * @param string          $number
     */
    private function setTicketCustomer(TicketInterface $ticket, $number)
    {
        $customer = $this
            ->repositoryFactory
            ->getRepository(CustomerInterface::class)
            ->findOneByNumber($number);

        if (null === $customer) {
            return;
        }

        $ticket->setCustomer($customer);
    }
}
