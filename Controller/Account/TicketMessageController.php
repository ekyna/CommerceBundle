<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Support\TicketMessageType;
use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Ekyna\Component\Resource\Model\Actions;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TicketMessageController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketMessageController extends AbstractTicketController
{
    /**
     * Ticket message new action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException("Only XHR is supported.");
        }

        $ticket = $this->findTicket($request);

        /** @var \Ekyna\Component\Commerce\Support\Model\TicketMessageInterface $message */
        $message = $this->get('ekyna_commerce.ticket_message.repository')->createNew();
        $message->setTicket($ticket);

        $this->denyAccessUnlessGranted(Actions::CREATE, $message);

        $form = $this->createForm(TicketMessageType::class, $message, [
            'action' => $this->generateUrl('ekyna_commerce_account_ticket_message_new', [
                'ticketId' => $ticket->getId(),
            ]),
            'method' => 'POST',
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->get('ekyna_commerce.ticket_message.operator')->create($message);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket'  => $ticket,
                    'message' => $message,
                ];

                $response = new Response($this->serialize($data));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $modal = $this
            ->createModal('ekyna_commerce.ticket_message.header.new', $form->createView())
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_message.html.twig',
            ]);

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * Ticket message edit action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function editAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException("Only XHR is supported.");
        }

        $message = $this->findMessage($request);

        $this->denyAccessUnlessGranted(Actions::EDIT, $message);

        $form = $this->createForm(TicketMessageType::class, $message, [
            'action' => $this->generateUrl('ekyna_commerce_account_ticket_message_edit', [
                'ticketId'        => $message->getTicket()->getId(),
                'ticketMessageId' => $message->getId(),
            ]),
            'method' => 'POST',
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->get('ekyna_commerce.ticket_message.operator')->update($message);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket'  => $message->getTicket(),
                    'message' => $message,
                ];

                $response = new Response($this->serialize($data));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $modal = $this
            ->createModal('ekyna_commerce.ticket_message.header.edit', $form->createView())
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_message.html.twig',
            ]);

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * Ticket message remove action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function removeAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException("Only XHR is supported.");
        }

        $message = $this->findMessage($request);

        $this->denyAccessUnlessGranted(Actions::DELETE, $message);

        $ticket = $message->getTicket();

        $form = $this->createForm(ConfirmType::class, null, [
            'action'  => $this->generateUrl('ekyna_commerce_account_ticket_message_remove', [
                'ticketId'        => $message->getTicket()->getId(),
                'ticketMessageId' => $message->getId(),
            ]),
            'method'  => 'POST',
            'attr'    => [
                'class' => 'form-horizontal',
            ],
            'message' => 'ekyna_commerce.ticket_message.message.remove_confirm',
            'buttons' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->get('ekyna_commerce.ticket_message.operator')->delete($message);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket'  => $ticket,
                    'success' => true,
                ];

                $response = new Response($this->serialize($data));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $modal = $this
            ->createModal('ekyna_commerce.ticket_message.header.remove', $form->createView(), 'confirm')
            ->setSize(Modal::SIZE_NORMAL)
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_confirm.html.twig',
            ]);

        return $this->get('ekyna_core.modal')->render($modal);
    }
}
