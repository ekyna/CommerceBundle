<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Support\TicketType;
use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Commerce\Support\Model\TicketStates;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
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
    private const REMOVE = 'remove';
    private const CLOSE = 'close';
    private const OPEN = 'open';

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
        return $this->handleConfirmation($request, self::REMOVE, function(TicketInterface $ticket) {
            return $this->get('ekyna_commerce.ticket.operator')->delete($ticket);
        });
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
        return $this->handleConfirmation($request, self::CLOSE, function(TicketInterface $ticket) {
            $ticket->setState(TicketStates::STATE_CLOSED);

            return $this->get('ekyna_commerce.ticket.operator')->update($ticket);
        });
    }

    /**
     * Ticket (re)open action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function openAction(Request $request)
    {
        return $this->handleConfirmation($request, self::OPEN, function(TicketInterface $ticket) {
            $ticket->setState(TicketStates::STATE_NEW);

            return $this->get('ekyna_commerce.ticket.operator')->update($ticket);
        });
    }

    /**
     * Handles the confirmation form.
     *
     * @param Request  $request
     * @param string   $action
     * @param callable $onConfirmed
     *
     * @return Response
     */
    public function handleConfirmation(Request $request, string $action, callable $onConfirmed): Response
    {
        if (!in_array($action, [self::OPEN, self::CLOSE, self::OPEN, true])) {
            throw new RuntimeException("Unexpected action");
        }

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException("Only XHR is supported.");
        }

        $ticket = $this->findTicket($request);

        $this->denyAccessUnlessGranted(Actions::EDIT, $ticket);

        $form = $this->createForm(ConfirmType::class, null, [
            'action'  => $this->generateUrl(sprintf('ekyna_commerce_account_ticket_%s', $action), [
                'ticketId' => $ticket->getId(),
            ]),
            'method'  => 'POST',
            'attr'    => [
                'class' => 'form-horizontal',
            ],
            'message' => sprintf('ekyna_commerce.ticket.message.%s_confirm', $action),
            'buttons' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ResourceEventInterface $event */
            $event = $onConfirmed($ticket);

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
            ->createModal(sprintf('ekyna_commerce.ticket.header.%s', $action), $form->createView(), 'confirm')
            ->setSize(Modal::SIZE_NORMAL)
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_confirm.html.twig',
            ]);

        return $this->get('ekyna_core.modal')->render($modal);
    }
}
