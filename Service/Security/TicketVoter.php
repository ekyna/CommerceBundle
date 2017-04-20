<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Security;

use Ekyna\Bundle\CommerceBundle\Model\TicketInterface;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Support\Model\TicketStates;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class TicketVoter
 * @package Ekyna\Bundle\CommerceBundle\Service\Security
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketVoter extends Voter
{
    /**
     * @inheritDoc
     *
     * @param TicketInterface $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($subject->isInternal()) {
            return false;
        }

        if ($subject->getState() === TicketStates::STATE_CLOSED) {
            return false;
        } elseif ($attribute === Permission::DELETE) {
            return false;
        }

        /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
        $customer = $subject->getCustomer();

        return $customer->getUser() === $user;
    }

    /**
     * @inheritDoc
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof TicketInterface;
    }
}
