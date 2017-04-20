<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Support\TicketType;
use Ekyna\Bundle\CommerceBundle\Model\QuoteInterface;
use Ekyna\Bundle\UiBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Commerce\Support\Model\TicketStates;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class SupportController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketController extends AbstractTicketController
{
    private const REMOVE = 'remove';
    private const CLOSE  = 'close';
    private const OPEN   = 'open';

    /**
     * Ticket index action.
     *
     * @return Response
     */
    public function index(): Response
    {
        $customer = $this->getCustomer();

        $content = $this->twig->render('@EkynaCommerce/Account/Ticket/index.html.twig', [
            'customer' => $customer,
        ]);

        return (new Response($content))->setPrivate();
    }

    public function create(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Only XHR is supported.');
        }

        $customer = $this->getCustomer();

        /** @var TicketInterface $ticket */
        $ticket = $this->factoryFactory->getFactory(TicketInterface::class)->create();
        $ticket->setCustomer($customer);

        $this->denyAccessUnlessGranted(Permission::CREATE, $ticket);

        $actionParameters = [
            'ticketId' => $ticket->getId(),
        ];

        if ($number = $request->query->get('order')) {
            $order = $this
                ->repositoryFactory
                ->getRepository(OrderInterface::class)
                ->findOneByCustomerAndNumber($customer, $number);

            if (null === $order) {
                throw new NotFoundHttpException('Order not found.');
            }

            $ticket->addOrder($order);
            $actionParameters['order'] = $order->getNumber();
        } elseif ($number = $request->query->get('quote')) {
            $quote = $this
                ->repositoryFactory
                ->getRepository(QuoteInterface::class)
                ->findOneByCustomerAndNumber($customer, $number);

            if (null === $quote) {
                throw new NotFoundHttpException('Quote not found.');
            }

            $ticket->addQuote($quote);
            $actionParameters['quote'] = $quote->getNumber();
        } else {
            throw new NotFoundHttpException('Missing order id or quote id query parameter.');
        }

        $form = $this->formFactory->create(TicketType::class, $ticket, [
            'action' => $this->urlGenerator->generate('ekyna_commerce_account_ticket_create', $actionParameters),
            'method' => 'POST',
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->managerFactory->getManager(TicketInterface::class)->create($ticket);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket' => $ticket,
                ];

                $response = new Response($this->serialize($data, ['Default', 'Ticket']));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            FormUtil::addErrorsFromResourceEvent($form, $event);
        }

        $modal = $this
            ->createModal('ticket.header.new')
            ->setForm($form->createView())
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_ticket.html.twig',
            ]);

        return $this->modalRenderer->render($modal)->setPrivate();
    }

    public function update(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Only XHR is supported.');
        }

        $ticket = $this->findTicket($request);

        $this->denyAccessUnlessGranted(Permission::UPDATE, $ticket);

        $form = $this->formFactory->create(TicketType::class, $ticket, [
            'action' => $this->urlGenerator->generate('ekyna_commerce_account_ticket_update', [
                'ticketId' => $ticket->getId(),
            ]),
            'method' => 'POST',
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->managerFactory->getManager(TicketInterface::class)->update($ticket);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket' => $ticket,
                ];

                $response = new Response($this->serialize($data));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            FormUtil::addErrorsFromResourceEvent($form, $event);
        }

        $modal = $this
            ->createModal('ticket.header.edit')
            ->setForm($form->createView())
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_ticket.html.twig',
            ]);

        return $this->modalRenderer->render($modal)->setPrivate();
    }

    public function delete(Request $request): Response
    {
        return $this->handleConfirmation($request, self::REMOVE, function (TicketInterface $ticket) {
            return $this->managerFactory->getManager(TicketInterface::class)->delete($ticket);
        });
    }

    public function close(Request $request): Response
    {
        return $this->handleConfirmation($request, self::CLOSE, function (TicketInterface $ticket) {
            $ticket->setState(TicketStates::STATE_CLOSED);

            return $this->managerFactory->getManager(TicketInterface::class)->save($ticket);
        });
    }

    public function open(Request $request): Response
    {
        return $this->handleConfirmation($request, self::OPEN, function (TicketInterface $ticket) {
            $ticket->setState(TicketStates::STATE_NEW);

            return $this->managerFactory->getManager(TicketInterface::class)->save($ticket);
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
            throw new RuntimeException('Unexpected action');
        }

        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Only XHR is supported.');
        }

        $ticket = $this->findTicket($request);

        $this->denyAccessUnlessGranted($action === self::REMOVE ? Permission::DELETE : Permission::UPDATE, $ticket);

        $form = $this->formFactory->create(ConfirmType::class, null, [
            'action'  => $this->urlGenerator->generate(sprintf('ekyna_commerce_account_ticket_%s', $action), [
                'ticketId' => $ticket->getId(),
            ]),
            'method'  => 'POST',
            'attr'    => [
                'class' => 'form-horizontal',
            ],
            'message' => t(sprintf('ticket.message.%s_confirm', $action), [], 'EkynaCommerce'),
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

            FormUtil::addErrorsFromResourceEvent($form, $event);
        }

        $modal = $this
            ->createModal(sprintf('ticket.header.%s', $action), 'confirm')
            ->setSize(Modal::SIZE_NORMAL)
            ->setForm($form->createView())
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_confirm.html.twig',
            ]);

        return $this->modalRenderer->render($modal)->setPrivate();
    }
}
