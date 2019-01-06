<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CommerceBundle\Model\TicketInterface;
use Ekyna\Bundle\CoreBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\CoreBundle\Modal\Modal;
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
    /**
     * Ticket close action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function closeAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Only XHR is supported');
        }

        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var TicketInterface $resource */
        $resource = $context->getResource($resourceName);

        $this->isGranted('EDIT', $resource);

        $form = $this->createForm(ConfirmType::class, null, [
            'action'  => $this->generateResourcePath($resource, 'close'),
            'method'  => 'POST',
            'attr'    => [
                'class' => 'form-horizontal',
            ],
            'message' => 'ekyna_commerce.ticket.message.close_confirm',
            'buttons' => false,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            $resource->setState(TicketStates::STATE_CLOSED);
            $event = $this->getOperator()->update($resource);

            if (!$event->hasErrors()) {
                return JsonResponse::create($this->normalize($resource));
            }

            // TODO all event messages should be bound to XHR response
            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }

        $title = sprintf('%s.header.close', $this->config->getTranslationPrefix());
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
