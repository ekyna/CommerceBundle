<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CommerceBundle\Form\Type\Notify\NotifyType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierOrderSubmitType;
use Ekyna\Bundle\CommerceBundle\Model\SubjectLabel;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderSubmit;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Ekyna\Component\Commerce\Supplier\Event\SupplierOrderEvents;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

        $submit = new SupplierOrderSubmit($resource);
        $submit->setEmails([$resource->getSupplier()->getEmail()]);

        $form = $this->createForm(SupplierOrderSubmitType::class, $submit, [
            'attr' => [
                'class' => 'form-horizontal',
            ],
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
            $dispatcher = $this->get('ekyna_resource.event_dispatcher');
            $event = $dispatcher->createResourceEvent($resource);
            $dispatcher->dispatch(SupplierOrderEvents::PRE_SUBMIT, $event);

            if (!$event->hasErrors()) {
                // TODO use ResourceManager
                $event = $this->getOperator()->update($resource);

                $event->toFlashes($this->getFlashBag());

                if (!$event->hasErrors()) {
                    if ($submit->isSendEmail()) {
                        if ($this->get('ekyna_commerce.mailer')->sendSupplierOrderSubmit($submit)) {
                            $this->addFlash('ekyna_commerce.supplier_order.message.submit.success', 'success');
                        } else {
                            $this->addFlash('ekyna_commerce.supplier_order.message.submit.failure', 'danger');
                        }
                    }

                    // TODO Post submit event ?

                    return $this->redirect($this->generateUrl(
                        $this->config->getRoute('show'),
                        $context->getIdentifiers(true)
                    ));
                }
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
     * Supplier order cancel action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cancelAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $this->isGranted('EDIT');

        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var SupplierOrderInterface $resource */
        $resource = $context->getResource($resourceName);

        if (SupplierOrderStates::isCancelableState($resource)) {
            $resource->setState(SupplierOrderStates::STATE_CANCELED);

            //$dispatcher = $this->get('ekyna_resource.event_dispatcher');
            //$event = $dispatcher->createResourceEvent($resource);
            //$dispatcher->dispatch(SupplierOrderEvents::PRE_SUBMIT, $event);

            $event = $this->getOperator()->update($resource);

            $event->toFlashes($this->getFlashBag());
        }

        return $this->redirect($this->generateUrl(
            $this->config->getRoute('show'),
            $context->getIdentifiers(true)
        ));
    }

    /**
     * Supplier order summary action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function summaryAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $context = $this->loadContext($request);
        /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface $supplierOrder */
        $supplierOrder = $context->getResource();

        $this->isGranted('VIEW', $supplierOrder);

        $response = new Response();
        $response->setVary(['Accept-Encoding', 'Accept']);
        $response->setExpires(new \DateTime('+3 min'));

        $html = false;

        $accept = $request->getAcceptableContentTypes();
        if (in_array('application/json', $accept, true)) {
            $response->headers->add(['Content-Type' => 'application/json']);
        } elseif (in_array('text/html', $accept, true)) {
            $html = true;
        } else {
            throw $this->createNotFoundException("Unsupported conten type.");
        }

        if ($html) {
            $content = $this->renderView(
                '@EkynaCommerce/Admin/SupplierOrder/summary.html.twig',
                $this->get('serializer')->normalize($supplierOrder, 'json', ['groups' => ['Summary']])
            );
        } else {
            $content = $this->get('serializer')->serialize($supplierOrder, 'json', ['groups' => ['Summary']]);
        }

        $response->setContent($content);

        return $response;
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

    /**
     * Label action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function labelAction(Request $request)
    {
        $context = $this->loadContext($request);

        /** @var SupplierOrderInterface $order */
        $order = $context->getResource();

        $this->isGranted('VIEW', $order);

        $ids = (array)$request->query->get('id', []);
        $ids = array_map(function ($id) {
            return intval($id);
        }, $ids);
        $ids = array_filter($ids, function ($id) {
            return 0 < $id;
        });

        $helper = $this->get('ekyna_commerce.subject_helper');
        $subjects = [];

        foreach ($order->getItems() as $item) {
            if (in_array(intval($item->getId()), $ids, true)) {
                $subjects[] = $helper->resolve($item);
            }
        }

        if (empty($subjects)) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $renderer = $this->get('ekyna_commerce.subject.label_renderer');

        $labels = $renderer->buildLabels($subjects);

        if (1 === count($labels) && !empty($geocode = $request->query->get('geocode'))) {
            $labels[0]->setGeocode($geocode);
        }

        $date = $order->getOrderedAt() ?? new \DateTime();
        $extra = sprintf('%s (%s)', $order->getNumber(), $date->format('Y-m-d'));
        foreach ($labels as $label) {
            $label->setExtra($extra);
        }

        $pdf = $renderer->render($labels, SubjectLabel::FORMAT_LARGE);

        return new Response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Supplier order notify action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function notifyAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not yet supported.');
        }

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var SupplierOrderInterface $resource */
        $resource = $context->getResource($resourceName);

        if ($resource->getState() === SupplierOrderStates::STATE_NEW) {
            $this->addFlash("Can't notify non submitted order.", 'warning');

            return $this->redirect($this->generateResourcePath($resource));
        }

        $builder = $this->get('ekyna_commerce.notify.builder');

        $notify = $builder->create(NotificationTypes::MANUAL, $resource);

        $builder->build($notify);

        $form = $this->createNotifyForm($resource, $notify);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('ekyna_commerce.notify.queue')->add($notify);

            $this->addFlash('ekyna_commerce.notify.message.sent', 'success');

            return $this->redirect($this->generateResourcePath($resource));
        }

        $this->appendBreadcrumb(
            sprintf('%s_transform', $resourceName),
            'ekyna_core.button.notify'
        );

        return $this->render(
            $this->config->getTemplate('notify.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * Supplier order template action.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function templateAction(Request $request)
    {
        $context = $this->loadContext($request);

        /** @var SupplierOrderInterface $order */
        $order = $context->getResource();

        $this->isGranted('VIEW', $order);

        /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierTemplateInterface $template */
        $template = $this
            ->get('ekyna_commerce.supplier_template.repository')
            ->find($request->attributes->get('id'));
        if (!$template) {
            throw $this->createNotFoundException("Template not found");
        }

        $formatter = $this
            ->get('ekyna_commerce.util.formatter_factory')
            ->create(null, $order->getCurrency()->getCode());

        $replacements = [
            '%number%' => $order->getNumber(),
            '%date%'   => $order->getOrderedAt() ? $formatter->date($order->getOrderedAt()) : null,
        ];

        $locale = $request->query->get('_locale', 'en');

        $translation = $template->translate($locale);

        return new JsonResponse([
            'subject' => strtr($translation->getSubject(), $replacements),
            'message' => strtr($translation->getMessage(), $replacements),
        ]);
    }

    /**
     * Creates the notify form.
     *
     * @param SupplierOrderInterface $order
     * @param Notify                 $notification
     * @param bool                   $footer
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createNotifyForm(SupplierOrderInterface $order, Notify $notification, $footer = true)
    {
        $action = $this->generateResourcePath($order, 'notify');

        $form = $this->createForm(NotifyType::class, $notification, [
            'source'            => $order,
            'action'            => $action,
            'attr'              => ['class' => 'form-horizontal'],
            'method'            => 'POST',
            'admin_mode'        => true,
            '_redirect_enabled' => true,
        ]);

        if ($footer) {
            $form->add('actions', FormActionsType::class, [
                'buttons' => [
                    'send'   => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'primary',
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
                                'href'  => $this->generateResourcePath($order),
                            ],
                        ],
                    ],
                ],
            ]);
        }

        return $form;
    }
}
