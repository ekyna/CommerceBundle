<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CommerceBundle\Model\SubjectChoice;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AbstractSaleController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleController extends ResourceController
{
    /**
     * Creates a sale item based on the subject choice.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function addSubjectAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Bundle\CommerceBundle\Model\OrderInterface $resource */
        $resource = $context->getResource($resourceName);

        $this->isGranted('EDIT', $resource);

        $isXhr = $request->isXmlHttpRequest();

        $data = new SubjectChoice();

        $action = $this->generateUrl('ekyna_commerce_order_item_admin_add', [
            'orderId' => $resource->getId()
        ]);

        $flow = $this->get('ekyna_commerce.add_subject.form_flow');
        $flow->setGenericFormOptions([
            'action' => $action,
            'method' => 'POST',
            'attr' => ['class' => 'form-horizontal'],
            'admin_mode' => true,
            '_redirect_enabled' => true,
        ]);
        $flow->bind($data);

        $form = $flow->createForm();

        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);

            if ($flow->nextStep()) {
                $form = $flow->createForm();
            } else {
                // Create the item with defaults based on subject choice
                $provider = $this
                    ->get('ekyna_commerce.subject.provider_registry')
                    ->getProvider($data->getType());

                $itemClass = $this->getParameter('ekyna_commerce.order_item.class');
                $item = new $itemClass();
                $resource->addItem($item); // So that we can access to order from item.

                $provider->setItemDefaults($item, $data->getChoice());

                // TODO use ResourceManager
                $event = $this->getOperator()->update($resource);
                if (!$isXhr) {
                    $event->toFlashes($this->getFlashBag());
                }

                if (!$event->hasErrors()) {
                    $flow->reset(); // remove step data from the session

                    if ($isXhr) {
                        // TODO default serialization
                        return JsonResponse::create([
                            'id' => $resource->getId(),
                            'name' => (string) $resource,
                        ]);
                    }

                    return $this->redirect($this->generateResourcePath($resource));
                } else {
                    foreach ($event->getErrors() as $error) {
                        $form->addError(new FormError($error->getMessage()));
                    }
                }

                // Redirect to order (TODO redirect to configure item subject ?)
                return $this->redirect($this->generateResourcePath($resource));
            }
        }

        if ($isXhr) {
            $modal = $this->createModal('edit');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars())
            ;
            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(sprintf('%s-new', 'ekyna_commerce_order_item'), 'ekyna_core.button.create');
//        $this->appendBreadcrumb(sprintf('%s-new', $resourceName), 'ekyna_core.button.create');

        return $this->render(
            $this->config->getTemplate('add_subject.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
                'flow' => $flow,
            ])
        );

        // 1. Type (provider) choice

        // 2. Subject choice

        // 3. Generate item defaults (use provider)

        // 4. Redirect to configure
    }

    /**
     * Creates a new sale item.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function newItemAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        $resource = $context->getResource($resourceName);

        $this->isGranted('EDIT', $resource);
    }

    /**
     * Configures the sale item subject.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function configureSubjectAction(Request $request)
    {

    }

    /**
     * Edits the sale item.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function editItemAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        $resource = $context->getResource($resourceName);

        $this->isGranted('EDIT', $resource);
    }

    /**
     * Removes the sale item.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function removeItemAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        $resource = $context->getResource($resourceName);

        $this->isGranted('EDIT', $resource);

        // TODO prevent locked item deletion
    }
}
