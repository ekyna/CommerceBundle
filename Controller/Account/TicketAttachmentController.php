<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Support\TicketAttachmentType;
use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Ekyna\Component\Resource\Model\Actions;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class TicketAttachmentController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketAttachmentController extends AbstractTicketController
{
    /**
     * Ticket attachment new action.
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

        $message = $this->findMessage($request);

        /** @var \Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface $attachment */
        $attachment = $this->get('ekyna_commerce.ticket_attachment.repository')->createNew();
        $attachment->setMessage($message);

        $this->denyAccessUnlessGranted(Actions::CREATE, $attachment);

        $form = $this->createForm(TicketAttachmentType::class, $attachment, [
            'action' => $this->generateUrl('ekyna_commerce_account_ticket_attachment_new', [
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
            $event = $this->get('ekyna_commerce.ticket_attachment.operator')->create($attachment);

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
            ->createModal('ekyna_commerce.attachment.header.new', $form->createView())
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_attachment.html.twig',
            ]);

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * Ticket attachment edit action.
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

        $attachment = $this->findAttachment($request);

        $this->denyAccessUnlessGranted(Actions::EDIT, $attachment);

        $message = $attachment->getMessage();
        if (!$message->isCustomer()) {
            throw $this->createAccessDeniedException("You cannot edit this attachment.");
        }

        $form = $this->createForm(TicketAttachmentType::class, $attachment, [
            'action' => $this->generateUrl('ekyna_commerce_account_ticket_attachment_edit', [
                'ticketId'           => $attachment->getMessage()->getTicket()->getId(),
                'ticketMessageId'    => $attachment->getMessage()->getId(),
                'ticketAttachmentId' => $attachment->getId(),
            ]),
            'method' => 'POST',
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->get('ekyna_commerce.ticket_attachment.operator')->update($attachment);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket'  => $message->getTicket(),
                    'message' => $attachment->getMessage(),
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
            ->createModal('ekyna_commerce.attachment.header.edit', $form->createView())
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_attachment.html.twig',
            ]);

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * Ticket attachment remove action.
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

        $attachment = $this->findAttachment($request);

        $this->denyAccessUnlessGranted(Actions::DELETE, $attachment);

        $message = $attachment->getMessage();
        if (!$message->isCustomer()) {
            throw $this->createAccessDeniedException("You cannot remove this attachment.");
        }

        $form = $this->createForm(ConfirmType::class, null, [
            'action'  => $this->generateUrl('ekyna_commerce_account_ticket_attachment_remove', [
                'ticketId'           => $attachment->getMessage()->getTicket()->getId(),
                'ticketMessageId'    => $attachment->getMessage()->getId(),
                'ticketAttachmentId' => $attachment->getId(),
            ]),
            'method'  => 'POST',
            'attr'    => [
                'class' => 'form-horizontal',
            ],
            'message' => 'ekyna_commerce.attachment.message.remove_confirm',
            'buttons' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->get('ekyna_commerce.ticket_attachment.operator')->delete($attachment);

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
            ->createModal('ekyna_commerce.attachment.header.remove', $form->createView(), 'confirm')
            ->setSize(Modal::SIZE_NORMAL)
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_confirm.html.twig',
            ]);

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * Ticket attachment download action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function downloadAction(Request $request)
    {
        $attachment = $this->findAttachment($request);

        $this->denyAccessUnlessGranted(Actions::VIEW, $attachment);

        $fs = $this->get('local_commerce_filesystem');
        if (!$fs->has($attachment->getPath())) {
            throw $this->createNotFoundException('File not found');
        }

        /** @var \League\Flysystem\File $file */
        $file = $fs->get($attachment->getPath());

        $response = new Response($file->read());
        $response->setPrivate();

        $response->headers->set('Content-Type', $file->getMimetype());
        $header = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $attachment->guessFilename()
        );
        $response->headers->set('Content-Disposition', $header);

        return $response;
    }
}
