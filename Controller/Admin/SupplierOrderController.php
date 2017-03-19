<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierOrderSubmitType;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SupplierOrderController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderController extends ResourceController
{
    /**
     * {@inheritdoc}
     */
    public function newAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Supplier order creation through XMLHttpRequest is not yet implemented.');
        }

        $this->isGranted('CREATE');

        $context = $this->loadContext($request);

        $resource = $this->createNew($context);
        $resourceName = $this->config->getResourceName();
        $context->addResource($resourceName, $resource);

        $flow = $this->get('ekyna_commerce.supplier_order.create_form_flow');
        $flow->setGenericFormOptions([
            'action'            => $this->generateResourcePath($resource, 'new'),
            'method'            => 'POST',
            'attr'              => ['class' => 'form-horizontal form-with-tabs'],
            'admin_mode'        => true,
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
                    return $this->redirect($this->generateUrl(
                        $this->config->getRoute('show'),
                        $context->getIdentifiers(true)
                    ));
                }
            }
        }

        $this->appendBreadcrumb(
            sprintf('%s_new', $resourceName),
            'ekyna_core.button.create'
        );

        return $this->render(
            $this->config->getTemplate('new.html'),
            $context->getTemplateVars([
                'flow' => $flow,
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * Supplier order submit action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function submitAction(Request $request)
    {
        $this->isGranted('EDIT');

        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var SupplierOrderInterface $resource */
        $resource = $context->getResource($resourceName);

        // TODO Use Symfony WorkFlow Component ? (can 'transition') (Sf 3.2)
        if ($resource->getState() !== SupplierOrderStates::STATE_NEW) {
            $this->addFlash('warning', 'ekyna_commerce.supplier_order.message.cant_be_submitted');
            return $this->redirect($this->generateResourcePath($resource));
        }

        $data = [
            'order'   => $resource,
            'emails'  => [$resource->getSupplier()->getEmail()],
            'message' => null,
            'confirm' => false,
        ];

        $form = $this->createForm(SupplierOrderSubmitType::class, $data);

        $cancelPath = $this->generateUrl(
            $this->config->getRoute('show'),
            $context->getIdentifiers(true)
        );

        $form->add('actions', FormActionsType::class, [
            'buttons' => [
                'send'   => [
                    'type'    => Type\SubmitType::class,
                    'options' => [
                        'button_class' => 'warning',
                        'label'        => 'ekyna_core.button.send',
                        'attr'         => ['icon' => 'envelope'],
                    ],
                ],
                'cancel' => [
                    'type'    => Type\ButtonType::class,
                    'options' => [
                        'label'        => 'ekyna_core.button.cancel',
                        'button_class' => 'default',
                        'as_link'      => true,
                        'attr'         => [
                            'class' => 'form-cancel-btn',
                            'icon'  => 'remove',
                            'href'  => $cancelPath,
                        ],
                    ],
                ],
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use a service (workflow ?) (same in behat supplier order context)
            $resource->setOrderedAt(new \DateTime());

            // TODO use ResourceManager
            $event = $this->getOperator()->update($resource);

            $event->toFlashes($this->getFlashBag());

            if (!$event->hasErrors()) {
                // TODO Send message

                return $this->redirect($this->generateUrl(
                    $this->config->getRoute('show'),
                    $context->getIdentifiers(true)
                ));
            }
        }

        $this->appendBreadcrumb(
            sprintf('%s_submit', $resourceName),
            'ekyna_commerce.supplier_order.button.submit'
        );

        return $this->render(
            $this->config->getTemplate('submit.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }
}
