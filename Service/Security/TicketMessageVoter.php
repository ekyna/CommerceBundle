<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Security;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\TicketMessageInterface;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Support\Model\TicketStates;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class TicketMessageVoter
 * @package Ekyna\Bundle\CommerceBundle\Service\Security
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketMessageVoter extends Voter
{
    /**
     * @inheritDoc
     *
     * @param TicketMessageInterface $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($subject->isInternal()) {
            return false;
        }

        if (null !== $subject->getAdmin()) {
            if ($attribute === Permission::READ) {
                return true;
            }

            return false;
        }

        $ticket = $subject->getTicket();

        if ($ticket->getState() === TicketStates::STATE_CLOSED) {
            if ($attribute !== Permission::READ) {
                return false;
            }
        }

        /** @var CustomerInterface $customer */
        $customer = $ticket->getCustomer();

        return $customer->getUser() === $user;
    }

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof TicketMessageInterface;
    }
}
