<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\InChargeSubjectInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\User\Service\UserProviderInterface;

/**
 * Class InChargeResolver
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InChargeResolver
{
    private UserProviderInterface $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * Updates the subject's 'in charge' user.
     *
     * @return bool Whether the subject has been updated.
     */
    public function update(InChargeSubjectInterface $subject): bool
    {
        if (null !== $subject->getInCharge()) {
            return false;
        }

        if (null === $inCharge = $this->resolve($subject)) {
            return false;
        }

        $subject->setInCharge($inCharge);

        return true;
    }

    /**
     * Resolves the given subject's 'in charge' user.
     */
    public function resolve(InChargeSubjectInterface $subject): ?UserInterface
    {
        $this->userProvider->reset();

        /** @var UserInterface $user */
        # TODO Inconsistency with Ekyna\Bundle\AdminBundle\Form\Type\UserChoiceType (role filter)
        if (null !== $user = $this->userProvider->getUser()) {
            return $user;
        }

        if ($subject instanceof SaleInterface) {
            return $this->resolveSale($subject);
        }

        if ($subject instanceof TicketInterface) {
            return $this->resolveTicket($subject);
        }

        return null;
    }

    /**
     * Resolves the given sale's 'in charge' user.
     */
    private function resolveSale(SaleInterface $sale): ?UserInterface
    {
        /** @var CustomerInterface $customer */
        if (null === $customer = $sale->getCustomer()) {
            return null;
        }

        if (null !== $inCharge = $customer->getInCharge()) {
            return $inCharge;
        }

        /** @var CustomerInterface $customer */
        if (null === $customer = $customer->getParent()) {
            return null;
        }

        return $customer->getInCharge();
    }

    private function resolveTicket(TicketInterface $ticket): ?UserInterface
    {
        foreach ($ticket->getOrders() as $order) {
            if (!$order instanceof InChargeSubjectInterface) {
                continue;
            }
            if ($inCharge = $order->getInCharge()) {
                return $inCharge;
            }
        }

        foreach ($ticket->getQuotes() as $quote) {
            if (!$quote instanceof InChargeSubjectInterface) {
                continue;
            }

            if ($inCharge = $quote->getInCharge()) {
                return $inCharge;
            }
        }

        if (($customer = $ticket->getCustomer()) && $customer instanceof InChargeSubjectInterface) {
            return $customer->getInCharge();
        }

        return null;
    }
}
