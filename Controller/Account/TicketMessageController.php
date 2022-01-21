<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Support\TicketMessageType;
use Ekyna\Bundle\UiBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function Symfony\Component\Translation\t;

/**
 * Class TicketMessageController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketMessageController extends AbstractTicketController
{
    public function create(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Only XHR is supported.');
        }

        $ticket = $this->findTicket($request);

        /** @var TicketMessageInterface $message */
        $message = $this->factoryFactory->getFactory(TicketMessageInterface::class)->create();
        $message->setTicket($ticket);

        $this->denyAccessUnlessGranted(Permission::CREATE, $message);

        $form = $this->formFactory->create(TicketMessageType::class, $message, [
            'action' => $this->urlGenerator->generate('ekyna_commerce_account_ticket_message_create', [
                'ticketId' => $ticket->getId(),
            ]),
            'method' => 'POST',
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->managerFactory->getManager(TicketMessageInterface::class)->create($message);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket'  => $ticket,
                    'ticketMessage' => $message,
                ];

                $response = new Response($this->serialize($data));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            FormUtil::addErrorsFromResourceEvent($form, $event);
        }

        $modal = $this
            ->createModal('ticket_message.header.new')
            ->setForm($form->createView())
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_message.html.twig',
            ]);

        return $this->modalRenderer->render($modal)->setPrivate();
    }

    public function update(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Only XHR is supported.');
        }

        $message = $this->findMessage($request);

        $this->denyAccessUnlessGranted(Permission::UPDATE, $message);

        $form = $this->formFactory->create(TicketMessageType::class, $message, [
            'action' => $this->urlGenerator->generate('ekyna_commerce_account_ticket_message_update', [
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
            $event = $this->managerFactory->getManager(TicketMessageInterface::class)->update($message);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket'  => $message->getTicket(),
                    'ticketMessage' => $message,
                ];

                $response = new Response($this->serialize($data));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            FormUtil::addErrorsFromResourceEvent($form, $event);
        }

        $modal = $this
            ->createModal('ticket_message.header.edit')
            ->setForm($form->createView())
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_message.html.twig',
            ]);

        return $this->modalRenderer->render($modal)->setPrivate();
    }

    public function delete(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Only XHR is supported.');
        }

        $message = $this->findMessage($request);

        $this->denyAccessUnlessGranted(Permission::DELETE, $message);

        $ticket = $message->getTicket();

        $form = $this->formFactory->create(ConfirmType::class, null, [
            'action'  => $this->urlGenerator->generate('ekyna_commerce_account_ticket_message_delete', [
                'ticketId'        => $message->getTicket()->getId(),
                'ticketMessageId' => $message->getId(),
            ]),
            'method'  => 'POST',
            'attr'    => [
                'class' => 'form-horizontal',
            ],
            'message' => t('ticket_message.message.remove_confirm', [], 'EkynaCommerce'),
            'buttons' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->managerFactory->getManager(TicketMessageInterface::class)->delete($message);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket'  => $ticket,
                    'success' => true,
                ];

                $response = new Response($this->serialize($data));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            FormUtil::addErrorsFromResourceEvent($form, $event);
        }

        $modal = $this
            ->createModal('ticket_message.header.remove', 'confirm')
            ->setSize(Modal::SIZE_NORMAL)
            ->setForm($form->createView())
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_confirm.html.twig',
            ]);

        return $this->modalRenderer->render($modal)->setPrivate();
    }
}
