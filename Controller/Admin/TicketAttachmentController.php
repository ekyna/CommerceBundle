<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class TicketAttachmentController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketAttachmentController extends ResourceController
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

        $context = $this->loadContext($request);

        /** @var \Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface $attachment */
        $attachment = $this->createNew($context);

        $resourceName = $this->config->getResourceName();
        $context->addResource($resourceName, $attachment);

        $form = $this->createForm($this->config->getFormType(), $attachment, [
            'action'     => $this->generateResourcePath($attachment, 'new'),
            'method'     => 'POST',
            'attr'       => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->getOperator()->create($attachment);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket'  => $attachment->getMessage()->getTicket(),
                    'message' => $attachment->getMessage(),
                ];

                $response = new Response(
                    $this->get('serializer')->serialize($data, 'json', ['groups' => ['Default']])
                );
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $modal = $this
            ->createModal('new')
            ->setContent($form->createView())
            ->setVars($context->getTemplateVars());

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

        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface $attachment */
        $attachment = $context->getResource($resourceName);

        $this->isGranted('EDIT', $attachment);

        $form = $this->createForm($this->config->getFormType(), $attachment, [
            'action'     => $this->generateResourcePath($attachment, 'edit'),
            'method'     => 'POST',
            'attr'       => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->getOperator()->update($attachment);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket'  => $attachment->getMessage()->getTicket(),
                    'message' => $attachment->getMessage(),
                ];

                $response = new Response(
                    $this->get('serializer')->serialize($data, 'json', ['groups' => ['Default']])
                );
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $modal = $this
            ->createModal('edit')
            ->setContent($form->createView())
            ->setVars($context->getTemplateVars());

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

        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface $attachment */
        $attachment = $context->getResource($resourceName);
        $message = $attachment->getMessage();

        $this->isGranted('DELETE', $attachment);

        $form = $this->createForm(ConfirmType::class, null, [
            'action'     => $this->generateResourcePath($attachment, 'remove'),
            'method'     => 'POST',
            'attr'       => [
                'class' => 'form-horizontal',
            ],
            'message'    => 'ekyna_commerce.ticket_attachment.message.remove_confirm',
            'buttons'    => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->getOperator()->delete($attachment);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket'  => $message->getTicket(),
                    'message' => $message,
                ];

                $response = new Response(
                    $this->get('serializer')->serialize($data, 'json', ['groups' => ['Default']])
                );
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }

            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $modal = $this
            ->createModal('remove')
            ->setSize(Modal::SIZE_NORMAL)
            ->setContent($form->createView())
            ->setVars([
                'form_template' => '@EkynaAdmin/Form/xhr_remove_form.html.twig',
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
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Support\Model\TicketAttachmentInterface $attachment */
        $attachment = $context->getResource($resourceName);

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
