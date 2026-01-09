<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Security;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\TicketInterface;
use Ekyna\Bundle\ResourceBundle\Service\Security\UserVoter;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Support\Model\TicketStates;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class TicketVoter
 * @package Ekyna\Bundle\CommerceBundle\Service\Security
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketVoter extends UserVoter
{
    /**
     * @inheritDoc
     *
     * @param TicketInterface $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        if ($subject->isInternal()) {
            return false;
        }

        if ($subject->getState() === TicketStates::STATE_CLOSED) {
            return false;
        } elseif ($attribute === Permission::DELETE) {
            return false;
        }

        /** @var CustomerInterface $customer */
        $customer = $subject->getCustomer();

        return $customer->getUser() === $token->getUser();
    }

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof TicketInterface;
    }

    protected function getUserClass(): string
    {
        return UserInterface::class;
    }
}
