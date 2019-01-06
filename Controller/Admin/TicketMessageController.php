<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TicketMessageController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketMessageController extends ResourceController
{
    /**
     * @inheritDoc
     */
    public function homeAction()
    {
        throw $this->createNotFoundException('Unavailable');
    }

    /**
     * @inheritDoc
     */
    public function listAction(Request $request)
    {
        throw $this->createNotFoundException('Unavailable');
    }

    /**
     * @inheritDoc
     */
    public function newAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException("Only XHR is supported.");
        }

        $this->isGranted('CREATE');

        $context = $this->loadContext($request);

        /** @var \Ekyna\Component\Commerce\Support\Model\TicketMessageInterface $message */
        $message = $this->createNew($context);

        $resourceName = $this->config->getResourceName();
        $context->addResource($resourceName, $message);

        $form = $this->createForm($this->config->getFormType(), $message, [
            'action'     => $this->generateResourcePath($message, 'new'),
            'method'     => 'POST',
            'admin_mode' => true,
            'attr'       => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->getOperator()->create($message);

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
            ->createModal('new')
            ->setContent($form->createView())
            ->setVars($context->getTemplateVars());

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * @inheritDoc
     */
    public function editAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException("Only XHR is supported.");
        }

        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Support\Model\TicketMessageInterface $message */
        $message = $context->getResource($resourceName);

        $this->isGranted('EDIT', $message);

        $form = $this->createForm($this->config->getFormType(), $message, [
            'action'     => $this->generateResourcePath($message, 'edit'),
            'method'     => 'POST',
            'admin_mode' => true,
            'attr'       => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->getOperator()->create($message);

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
            ->createModal('edit')
            ->setContent($form->createView())
            ->setVars($context->getTemplateVars());

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * @inheritDoc
     */
    public function removeAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException("Only XHR is supported.");
        }

        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Support\Model\TicketMessageInterface $message */
        $message = $context->getResource($resourceName);
        $ticket = $message->getTicket();

        $this->isGranted('DELETE', $message);

        $form = $this->createForm(ConfirmType::class, null, [
            'action'     => $this->generateResourcePath($message, 'remove'),
            'method'     => 'POST',
            'admin_mode' => true,
            'attr'       => [
                'class' => 'form-horizontal',
            ],
            'message'    => 'ekyna_commerce.ticket_message.message.remove_confirm',
            'buttons'    => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->getOperator()->delete($message);

            if (!$event->hasErrors()) {
                $data = [
                    'ticket'  => $ticket,
                    'success' => true,
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
}
