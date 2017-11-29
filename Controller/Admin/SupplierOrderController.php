<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierOrderSubmitType;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderSubmit;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererInterface;
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
     * @inheritdoc
     */
    public function newAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Supplier order creation through XMLHttpRequest is not yet implemented.');
        }

        $this->isGranted('CREATE');

        $context = $this->loadContext($request);

        /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface $resource */
        $resource = $this->createNew($context);
        $resourceName = $this->config->getResourceName();
        $context->addResource($resourceName, $resource);

        $actionParams = [];

        if (0 < $supplierId = intval($request->query->get('supplierId'))) {
            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierInterface $supplier */
            if (null !== $supplier = $this->get('ekyna_commerce.supplier.repository')->find($supplierId)) {
                $resource->setSupplier($supplier);
                $actionParams['supplierId'] = $supplier->getId();

                $this->getOperator()->initialize($resource);
            }
        }

        $flow = $this->get('ekyna_commerce.supplier_order.create_form_flow');
        $flow->setGenericFormOptions([
            'action'            => $this->generateResourcePath($resource, 'new', $actionParams),
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
                    $flow->reset();

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
            $this->addFlash('ekyna_commerce.supplier_order.message.cant_be_submitted', 'warning');
            return $this->redirect($this->generateResourcePath($resource));
        }

        $submit = new SupplierOrderSubmit();
        $submit
            ->setOrder($resource)
            ->setEmails([$resource->getSupplier()->getEmail()])
            ->setMessage(
                $this->getTranslator()->trans(
                    'ekyna_commerce.supplier_order.message.submit.default', [
                        '%number%' => $resource->getNumber(),
                    ]
                )
            );

        $form = $this->createForm(SupplierOrderSubmitType::class, $submit, [
            'attr' => [
                'class' => 'form-horizontal',
            ]
        ]);

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
                if ($submit->isSendEmail()) {
                    if ($this->sendSubmitMessage($submit)) {
                        $this->addFlash('ekyna_commerce.supplier_order.message.submit.success', 'success');
                    } else {
                        $this->addFlash('ekyna_commerce.supplier_order.message.submit.failure', 'danger');
                    }
                }

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

    /**
     * Sends the supplier order submit message.
     *
     * @param SupplierOrderSubmit $submit
     *
     * @return bool
     */
    private function sendSubmitMessage(SupplierOrderSubmit $submit)
    {
        $order = $submit->getOrder();

        $settings = $this->container->get('ekyna_setting.manager');
        $fromEmail = $settings->getParameter('notification.from_email');
        $fromName = $settings->getParameter('notification.from_name');

        /** @var \Swift_Mime_Message $message */
        $message = new \Swift_Message();
        $message
            ->setSubject('Bon de commande ' . $order->getNumber())
            ->setFrom($fromEmail, $fromName)
            ->setTo($submit->getEmails())
            ->setBody($submit->getMessage(), 'text/html');

        $renderer = $this
            ->get('ekyna_commerce.document.renderer_factory')
            ->createRenderer($order);

        $message->attach(\Swift_Attachment::newInstance(
            $renderer->render(RendererInterface::FORMAT_PDF),
            $order->getNumber(),
            'application/pdf'
        ));

        return 0 < $this->get('mailer')->send($message);
    }

    /**
     * Render action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderAction(Request $request)
    {
        $context = $this->loadContext($request);

        /** @var SupplierOrderInterface $order */
        $order = $context->getResource();

        $this->isGranted('VIEW', $order);

        $renderer = $this
            ->get('ekyna_commerce.document.renderer_factory')
            ->createRenderer($order);

        return $renderer->respond($request);
    }
}
