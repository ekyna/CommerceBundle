<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Support\TicketType;
use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Ekyna\Component\Commerce\Support\Model\TicketStates;
use Ekyna\Component\Resource\Model\Actions;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SupportController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketController extends AbstractTicketController
{
    /**
     * Ticket index action.
     *
     * @return Response
     */
    public function indexAction()
    {
        $customer = $this->getCustomerOrRedirect();

        return $this->render('@EkynaCommerce/Account/Ticket/index.html.twig', [
            'customer' => $customer,
        ]);
    }

    /**
     * Ticket new action.
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

        $customer = $this->getCustomerOrRedirect();

        /** @var \Ekyna\Component\Commerce\Support\Model\TicketInterface $ticket */
        $ticket = $this->get('ekyna_commerce.ticket.repository')->createNew();
        $ticket->setCustomer($customer);

        $this->denyAccessUnlessGranted(Actions::CREATE, $ticket);

        $actionParameters = [
            'ticketId' => $ticket->getId(),
        ];

        if ($number = $request->query->get('order')) {
            $order = $this
                ->get('ekyna_commerce.order.repository')
                ->findOneByCustomerAndNumber($customer, $number);

            if (null === $order) {
                throw $this->createNotFoundException('Order not found.');
            }

            $ticket->addOrder($order);
            $actionParameters['order'] = $order->getNumber();
        } elseif ($number = $request->query->get('quote')) {
            $quote = $this
                ->get('ekyna_commerce.quote.repository')
                ->findOneByCustomerAndNumber($customer, $number);

            if (null === $quote) {
                throw $this->createNotFoundException('Quote not found.');
            }

            $ticket->addQuote($quote);
            $actionParameters['quote'] = $quote->getNumber();
        } else {
            throw $this->createNotFoundException('Missing order id or quote id query parameter.');
        }

        $form = $this->createForm(TicketType::class, $ticket, [
            'action' => $this->generateUrl('ekyna_commerce_account_ticket_new', $actionParameters),
            'method' => 'POST',
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->get('ekyna_commerce.ticket.operator')->create($ticket);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket' => $ticket,
                ];

                $response = new Response($this->serialize($data, ['Default', 'Ticket']));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $modal = $this
            ->createModal('ekyna_commerce.ticket.header.new', $form->createView())
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_ticket.html.twig',
            ]);

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * Ticket edit action.
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

        $ticket = $this->findTicket($request);

        $this->denyAccessUnlessGranted(Actions::EDIT, $ticket);

        $form = $this->createForm(TicketType::class, $ticket, [
            'action' => $this->generateUrl('ekyna_commerce_account_ticket_edit', [
                'ticketId' => $ticket->getId(),
            ]),
            'method' => 'POST',
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->get('ekyna_commerce.ticket.operator')->update($ticket);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket' => $ticket,
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
            ->createModal('ekyna_commerce.ticket.header.edit', $form->createView())
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_ticket.html.twig',
            ]);

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * Ticket remove action.
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

        $ticket = $this->findTicket($request);

        $this->denyAccessUnlessGranted(Actions::DELETE, $ticket);

        $form = $this->createForm(ConfirmType::class, null, [
            'action'  => $this->generateUrl('ekyna_commerce_account_ticket_remove', [
                'ticketId' => $ticket->getId(),
            ]),
            'method'  => 'POST',
            'attr'    => [
                'class' => 'form-horizontal',
            ],
            'message' => 'ekyna_commerce.ticket.message.remove_confirm',
            'buttons' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->get('ekyna_commerce.ticket.operator')->delete($ticket);

            if (!$event->hasErrors()) {
                $data = [
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
            ->createModal('ekyna_commerce.ticket.header.remove', $form->createView(), 'confirm')
            ->setSize(Modal::SIZE_NORMAL)
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_confirm.html.twig',
            ]);

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * Ticket close action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function closeAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException("Only XHR is supported.");
        }

        $ticket = $this->findTicket($request);

        $this->denyAccessUnlessGranted(Actions::EDIT, $ticket);

        $form = $this->createForm(ConfirmType::class, null, [
            'action'  => $this->generateUrl('ekyna_commerce_account_ticket_close', [
                'ticketId' => $ticket->getId(),
            ]),
            'method'  => 'POST',
            'attr'    => [
                'class' => 'form-horizontal',
            ],
            'message' => 'ekyna_commerce.ticket.message.close_confirm',
            'buttons' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->setState(TicketStates::STATE_CLOSED);
            $event = $this->get('ekyna_commerce.ticket.operator')->update($ticket);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket' => $ticket,
                ];

                $response = new Response($this->serialize($data, ['Default', 'Ticket']));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $modal = $this
            ->createModal('ekyna_commerce.ticket.header.close', $form->createView(), 'confirm')
            ->setSize(Modal::SIZE_NORMAL)
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_confirm.html.twig',
            ]);

        return $this->get('ekyna_core.modal')->render($modal);
    }
}
