<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ShipmentMethodController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodController extends Controller\ResourceController
{
    use Controller\Resource\ToggleableTrait,
        Controller\Resource\SortableTrait;

    /**
     * {@inheritdoc}
     */
    public function newAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw $this->createNotFoundException(
                'Shipment method creation through XMLHttpRequest is not yet implemented.'
            );
        }

        $this->isGranted('CREATE');

        $context = $this->loadContext($request);

        $resource = $this->createNew($context);
        $resourceName = $this->config->getResourceName();
        $context->addResource($resourceName, $resource);

        $this->getOperator()->initialize($resource);

        $flow = $this->get('ekyna_commerce.shipment_method.create_form_flow');
        $flow->setGenericFormOptions([
            'action'            => $this->generateResourcePath($resource, 'new'),
            'method'            => 'POST',
            'attr'              => ['class' => 'form-horizontal form-with-tabs'],
            '_redirect_enabled' => true,
        ]);
        $flow->bind($resource);

        $form = $flow->createForm();
        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);

            if ($flow->nextStep()) {
                // form for the next step
                $form = $flow->createForm();
            } else {
                // TODO use ResourceManager
                $event = $this->getOperator()->create($resource);

                $event->toFlashes($this->getFlashBag());

                if (!$event->hasErrors()) {
                    $flow->reset();

                    return $this->redirect($this->generateUrl(
                        $this->config->getRoute('show'),
                        $context->getIdentifiers(true)
                    ));
                }
            }
        }

        $this->appendBreadcrumb(sprintf('%s_new', $resourceName), 'ekyna_core.button.create');

        return $this->render(
            $this->config->getTemplate('new.html'),
            $context->getTemplateVars([
                'flow' => $flow,
                'form' => $form->createView()
            ])
        );
    }
}
