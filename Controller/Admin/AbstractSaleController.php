<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
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
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $resource */
        $resource = $context->getResource($resourceName);

        $this->isGranted('EDIT', $resource);

        $isXhr = $request->isXmlHttpRequest();

        $itemClass = $this->getParameter('ekyna_commerce.order_item.class');
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
        $item = new $itemClass();
        // So that we can access to the sale from the item.
        $resource->addItem($item);

        $action = $this->generateUrl('ekyna_commerce_order_item_admin_add', [
            'orderId' => $resource->getId()
        ]);

        $flow = $this->get('ekyna_commerce.add_item.form_flow');
        $flow->setGenericFormOptions([
            'action' => $action,
            'method' => 'POST',
            'attr' => ['class' => 'form-horizontal'],
            'admin_mode' => true,
            '_redirect_enabled' => true,
        ]);
        $flow->bind($item);

        $form = $flow->createForm();

        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);

            if ($flow->nextStep()) {
                $form = $flow->createForm();
            } else {
                // TODO validation

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

                return $this->redirect($this->generateResourcePath($resource));
            }
        }

        if ($isXhr) {
            $modal = $this->createModal('edit');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars());

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
    }
}
