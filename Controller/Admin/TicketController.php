<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CommerceBundle\Model\TicketInterface;
use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Support\Model\TicketStates;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TicketController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketController extends ResourceController
{
    private const OPEN  = 'open';
    private const CLOSE = 'close';

    /**
     * Ticket close action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function closeAction(Request $request): Response
    {
        return $this->handleConfirmation($request, self::CLOSE, function(TicketInterface $ticket) {
            $ticket->setState(TicketStates::STATE_CLOSED);
        });
    }

    /**
     * Ticket reopen action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function openAction(Request $request): Response
    {
        return $this->handleConfirmation($request, self::OPEN, function(TicketInterface $ticket) {
            $ticket->setState(TicketStates::STATE_NEW);
        });
    }

    /**
     * Handles the action confirmation.
     *
     * @param Request $request
     * @param string  $action
     * @param callable $onConfirmed
     *
     * @return Response
     */
    protected function handleConfirmation(Request $request, string $action, callable $onConfirmed): Response
    {
        if (!in_array($action, [self::OPEN, self::CLOSE, true])) {
            throw new RuntimeException("Unexpected action");
        }

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Only XHR is supported');
        }

        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var TicketInterface $resource */
        $resource = $context->getResource($resourceName);

        $this->isGranted('EDIT', $resource);

        $form = $this->createForm(ConfirmType::class, null, [
            'action'  => $this->generateResourcePath($resource, $action),
            'method'  => 'POST',
            'attr'    => [
                'class' => 'form-horizontal',
            ],
            'message' => sprintf('ekyna_commerce.ticket.message.%s_confirm', $action),
            'buttons' => false,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $onConfirmed($resource);

            // TODO use ResourceManager
            $event = $this->getOperator()->update($resource);

            if (!$event->hasErrors()) {
                return JsonResponse::create($this->normalize($resource));
            }

            // TODO all event messages should be bound to XHR response
            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $title = sprintf('%s.header.%s', $this->config->getTranslationPrefix(), $action);
        $vars = $context->getTemplateVars();
        unset($vars['form_template']);
        $modal = $this->createModal('confirm', $title, $resource);
        $modal
            ->setSize(Modal::SIZE_NORMAL)
            ->setContent($form->createView())
            ->setVars($vars);

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * @inheritDoc
     */
    protected function normalize($data, $format = 'json', array $context = null)
    {
        if ($data instanceof TicketInterface) {
            $data = ['ticket' => $data];
        }

        return parent::normalize($data, $format, $context);
    }
}
