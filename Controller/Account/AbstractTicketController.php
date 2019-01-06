<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Model\TicketInterface;
use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AbstractTicketController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractTicketController extends AbstractController
{
    /**
     * Finds the ticket.
     *
     * @param Request $request
     *
     * @return TicketInterface
     */
    protected function findTicket(Request $request)
    {
        $ticket = $this
            ->get('ekyna_commerce.ticket.repository')
            ->find($request->attributes->get('ticketId'));

        if (null === $ticket) {
            throw new NotFoundHttpException("Ticket not found.");
        }

        /** @noinspection PhpParamsInspection */
        $this->checkTicketOwner($ticket);

        return $ticket;
    }

    /**
     * Finds the message.
     *
     * @param Request $request
     *
     * @return TicketMessageInterface
     */
    protected function findMessage(Request $request)
    {
        /** @var TicketMessageInterface $message */
        $message = $this
            ->get('ekyna_commerce.ticket_message.repository')
            ->find($request->attributes->get('ticketMessageId'));

        if (null === $message) {
            throw new NotFoundHttpException("Ticket message not found.");
        }

        $ticket = $message->getTicket();
        if ($request->attributes->get('ticketId') != $ticket->getId()) {
            throw new NotFoundHttpException("Ticket message not found.");
        }

        /** @noinspection PhpParamsInspection */
        $this->checkTicketOwner($ticket);

        return $message;
    }

    /**
     * Finds the attachment.
     *
     * @param Request $request
     *
     * @return TicketAttachmentInterface
     */
    protected function findAttachment(Request $request)
    {
        /** @var TicketAttachmentInterface $attachment */
        $attachment = $this
            ->get('ekyna_commerce.ticket_attachment.repository')
            ->find($request->attributes->get('ticketAttachmentId'));

        if (null === $attachment) {
            throw new NotFoundHttpException("Ticket attachment not found.");
        }

        $message = $attachment->getMessage();
        if ($request->attributes->get('ticketMessageId') != $message->getId()) {
            throw new NotFoundHttpException("Ticket attachment not found.");
        }

        $ticket = $message->getTicket();
        if ($request->attributes->get('ticketId') != $ticket->getId()) {
            throw new NotFoundHttpException("Ticket attachment not found.");
        }

        /** @noinspection PhpParamsInspection */
        $this->checkTicketOwner($ticket);

        return $attachment;
    }

    /**
     * Checks that the given ticket belongs to the logged customer.
     *
     * @param TicketInterface $ticket
     *
     * @throws \Ekyna\Bundle\CoreBundle\Exception\RedirectException
     */
    protected function checkTicketOwner(TicketInterface $ticket)
    {
        if ($ticket->getCustomer() !== $this->getCustomerOrRedirect()) {
            throw $this->createNotFoundException('Ticket not found');
        }
    }

    /**
     * Serializes the given data.
     *
     * @param array $data
     * @param array $groups
     *
     * @return string
     */
    protected function serialize(array $data, array $groups = ['Default'])
    {
        return $this->get('serializer')->serialize($data, 'json', ['groups' => $groups]);
    }

    /**
     * Creates a modal.
     *
     * @param string $title
     * @param mixed  $content
     * @param string $button
     *
     * @return Modal
     */
    protected function createModal($title, $content = null, string $button = null)
    {
        $modal = new Modal($title, $content);

        $buttons = [];

        if ($button === 'confirm') {
            $buttons['submit'] = [
                'id'       => 'submit',
                'label'    => 'ekyna_core.button.confirm',
                'icon'     => 'glyphicon glyphicon-ok',
                'cssClass' => 'btn-danger',
                'autospin' => true,
            ];
        } else {
            $buttons['submit'] = [
                'id'       => 'submit',
                'label'    => 'ekyna_core.button.save',
                'icon'     => 'glyphicon glyphicon-ok',
                'cssClass' => 'btn-success',
                'autospin' => true,
            ];
        }

        $buttons['close'] = [
            'id'       => 'close',
            'label'    => 'ekyna_core.button.cancel',
            'icon'     => 'glyphicon glyphicon-remove',
            'cssClass' => 'btn-default',
        ];

        $modal->setButtons($buttons);

        return $modal;
    }
}
