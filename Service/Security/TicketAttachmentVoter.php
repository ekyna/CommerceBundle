<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Security;

use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface;
use Ekyna\Component\Commerce\Support\Model\TicketStates;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class TicketAttachmentVoter
 * @package Ekyna\Bundle\CommerceBundle\Service\Security
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketAttachmentVoter extends Voter
{
    /**
     * @inheritDoc
     *
     * @param TicketAttachmentInterface $subject
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

        /** @var \Ekyna\Bundle\CommerceBundle\Model\TicketMessageInterface $message */
        $message = $subject->getMessage();
        if (null !== $message->getAdmin()) {
            if ($attribute === Permission::READ) {
                return true;
            }

            return false;
        }

        $ticket = $subject->getMessage()->getTicket();

        if ($ticket->getState() === TicketStates::STATE_CLOSED) {
            if ($attribute !== Permission::READ) {
                return false;
            }
        }

        /** @var \Ekyna\Bundle\CommerceBundle\Model\CustomerInterface $customer */
        $customer = $ticket->getCustomer();

        return $customer->getUser() === $user;
    }

    /**
     * @inheritDoc
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof TicketAttachmentInterface;
    }
}
