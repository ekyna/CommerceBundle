<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Support\TicketAttachmentType;
use Ekyna\Bundle\ResourceBundle\Service\Filesystem\FilesystemHelper;
use Ekyna\Bundle\UiBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface;
use Ekyna\Component\Resource\Action\Permission;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function Symfony\Component\Translation\t;

/**
 * Class TicketAttachmentController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketAttachmentController extends AbstractTicketController
{
    private Filesystem $filesystem;

    public function setFilesystem(Filesystem $filesystem): void
    {
        $this->filesystem = $filesystem;
    }

    public function create(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Only XHR is supported.');
        }

        $message = $this->findMessage($request);

        /** @var TicketAttachmentInterface $attachment */
        $attachment = $this->factoryFactory->getFactory(TicketAttachmentInterface::class)->create();
        $attachment->setMessage($message);

        $this->denyAccessUnlessGranted(Permission::CREATE, $attachment);

        $form = $this->formFactory->create(TicketAttachmentType::class, $attachment, [
            'action' => $this->urlGenerator->generate('ekyna_commerce_account_ticket_attachment_create', [
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
            $event = $this->managerFactory->getManager(TicketAttachmentInterface::class)->create($attachment);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket'  => $message->getTicket(),
                    'message' => $message,
                ];

                $response = new Response($this->serialize($data));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            FormUtil::addErrorsFromResourceEvent($form, $event);
        }

        $modal = $this
            ->createModal('attachment.header.new')
            ->setForm($form->createView())
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_attachment.html.twig',
            ]);

        return $this->modalRenderer->render($modal)->setPrivate();
    }

    public function update(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Only XHR is supported.');
        }

        $attachment = $this->findAttachment($request);

        $this->denyAccessUnlessGranted(Permission::UPDATE, $attachment);

        $message = $attachment->getMessage();
        if (!$message->isCustomer()) {
            throw new AccessDeniedHttpException('You cannot edit this attachment.');
        }

        $form = $this->formFactory->create(TicketAttachmentType::class, $attachment, [
            'action' => $this->urlGenerator->generate('ekyna_commerce_account_ticket_attachment_update', [
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
            $event = $this->managerFactory->getManager(TicketAttachmentInterface::class)->update($attachment);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket'  => $message->getTicket(),
                    'message' => $attachment->getMessage(),
                ];

                $response = new Response($this->serialize($data));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            FormUtil::addErrorsFromResourceEvent($form, $event);
        }

        $modal = $this
            ->createModal('attachment.header.edit')
            ->setForm($form->createView())
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_attachment.html.twig',
            ]);

        return $this->modalRenderer->render($modal)->setPrivate();
    }

    public function delete(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Only XHR is supported.');
        }

        $attachment = $this->findAttachment($request);

        $this->denyAccessUnlessGranted(Permission::DELETE, $attachment);

        $message = $attachment->getMessage();
        if (!$message->isCustomer()) {
            throw new AccessDeniedHttpException('You cannot remove this attachment.');
        }

        $form = $this->formFactory->create(ConfirmType::class, null, [
            'action'  => $this->urlGenerator->generate('ekyna_commerce_account_ticket_attachment_delete', [
                'ticketId'           => $attachment->getMessage()->getTicket()->getId(),
                'ticketMessageId'    => $attachment->getMessage()->getId(),
                'ticketAttachmentId' => $attachment->getId(),
            ]),
            'method'  => 'POST',
            'attr'    => [
                'class' => 'form-horizontal',
            ],
            'message' => t('attachment.message.remove_confirm', [], 'EkynaCommerce'),
            'buttons' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->managerFactory->getManager(TicketAttachmentInterface::class)->delete($attachment);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket'  => $message->getTicket(),
                    'message' => $message,
                ];

                $response = new Response($this->serialize($data));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            FormUtil::addErrorsFromResourceEvent($form, $event);
        }

        $modal = $this
            ->createModal('attachment.header.remove', 'confirm')
            ->setForm($form->createView())
            ->setSize(Modal::SIZE_NORMAL)
            ->setVars([
                'form_template' => '@EkynaCommerce/Account/Ticket/form_confirm.html.twig',
            ]);

        return $this->modalRenderer->render($modal)->setPrivate();
    }

    public function download(Request $request): Response
    {
        $attachment = $this->findAttachment($request);

        $this->denyAccessUnlessGranted(Permission::READ, $attachment);

        $helper = new FilesystemHelper($this->filesystem);

        if (!$helper->fileExists($attachment->getPath(), false)) {
            throw new NotFoundHttpException('File not found');
        }

        return $helper->createFileResponse($attachment->getPath(), $request, true)->setPrivate();
    }
}
