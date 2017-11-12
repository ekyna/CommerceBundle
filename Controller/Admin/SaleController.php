<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\CommerceBundle\Form\Type\Notification\NotificationType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleShipmentType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleTransformType;
use Ekyna\Bundle\CommerceBundle\Model\Notification;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartStates;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\TransformationTargets;
use Ekyna\Component\Commerce\Common\Util\AddressUtil;
use Ekyna\Component\Commerce\Document\Model\Document;
use Ekyna\Component\Commerce\Document\Util\SaleDocumentUtil;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteStates;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SaleController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleController extends AbstractSaleController
{
    /**
     * @inheritDoc
     */
    protected function createNew(Context $context)
    {
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = parent::createNew($context);

        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $customer */
        $customer = $this
            ->get('ekyna_commerce.customer.repository')
            ->find($context->getRequest()->query->get('customer'));

        if (null !== $customer) {
            $sale->setCustomer($customer);

            if (null !== $address = $customer->getDefaultInvoiceAddress(true)) {
                $invoiceAddress = $this->get('ekyna_commerce.sale_factory')->createAddressForSale($sale);
                AddressUtil::copy($address, $invoiceAddress);
                $sale->setInvoiceAddress($invoiceAddress);
            }

            if (null !== $address = $customer->getDefaultDeliveryAddress(true)) {
                $deliveryAddress = $this->get('ekyna_commerce.sale_factory')->createAddressForSale($sale);
                AddressUtil::copy($address, $deliveryAddress);
                $sale->setInvoiceAddress($deliveryAddress);
            }
        }

        return $sale;
    }

    /**
     * @inheritdoc
     */
    protected function buildShowData(array &$data, Context $context)
    {
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource();

        if ($sale instanceof CartInterface && $sale->getState() === CartStates::STATE_ACCEPTED) {
            $this->addFlash('ekyna_commerce.cart.message.transformation_to_order_is_ready');
        } elseif ($sale instanceof QuoteInterface && $sale->getState() === QuoteStates::STATE_ACCEPTED) {
            $this->addFlash('ekyna_commerce.quote.message.transformation_to_order_is_ready');
        }

        $data['sale_view'] = $this->buildSaleView($sale);

        return null;
    }

    /**
     * Edit the sale shipment data.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editShipmentAction(Request $request)
    {
        /*
         * Form with:
         * - Delivery country
         * - Estimated shipment price
         * - Preferred shipment method choice
         */

        $context = $this->loadContext($request);
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource();

        $this->isGranted('EDIT', $sale);

        $isXhr = $request->isXmlHttpRequest();
        $form = $this->createShipmentEditForm($sale, !$isXhr);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->update($sale);
            if (!$isXhr) {
                $event->toFlashes($this->getFlashBag());
            }

            if (!$event->hasErrors()) {
                if ($isXhr) {
                    return $this->buildXhrSaleViewResponse($sale);
                }

                return $this->redirect($this->generateResourcePath($sale));
            } elseif ($isXhr) {
                // TODO all event messages should be bound to XHR response
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }
        }

        $formTemplate = 'EkynaCommerceBundle:Admin/Common/Sale:_form_edit_shipment.html.twig';

        if ($isXhr) {
            $modal = $this->createModal('edit', 'ekyna_commerce.sale.header.shipment.edit');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars([
                    'form_template' => $formTemplate,
                ]));

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_edit_shipment', $sale),
            'ekyna_commerce.sale.header.shipment.edit'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Sale:edit_shipment.html.twig',
            $context->getTemplateVars([
                'form'          => $form->createView(),
                'form_template' => $formTemplate,
            ])
        );
    }

    /**
     * Creates the shipment edit form.
     *
     * @param SaleInterface $sale
     * @param bool          $footer
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createShipmentEditForm(SaleInterface $sale, $footer = true)
    {
        $action = $this->generateResourcePath($sale, 'edit_shipment');

        $form = $this->createForm(SaleShipmentType::class, $sale, [
            'action'            => $action,
            'attr'              => ['class' => 'form-horizontal'],
            'method'            => 'POST',
            'admin_mode'        => true,
            '_redirect_enabled' => true,
        ]);

        if ($footer) {
            $form->add('actions', FormActionsType::class, [
                'buttons' => [
                    'remove' => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'warning',
                            'label'        => 'ekyna_core.button.transform',
                            'attr'         => ['icon' => 'ok'],
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
                                'href'  => $this->generateResourcePath($sale),
                            ],
                        ],
                    ],
                ],
            ]);
        }

        return $form;
    }

    /**
     * Refresh the sale view (updates the items quantities).
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function refreshAction(Request $request)
    {
        if (!($request->isXmlHttpRequest() && $request->getMethod() === 'GET')) {
            throw new NotFoundHttpException();
        }

        $context = $this->loadContext($request);
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource();

        return $this->buildXhrSaleViewResponse($sale);
    }

    /**
     * Recalculate (updates the items quantities).
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function recalculateAction(Request $request)
    {
        if (!($request->isXmlHttpRequest() && $request->getMethod() === 'POST')) {
            throw new NotFoundHttpException();
        }

        $context = $this->loadContext($request);
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource();

        $form = $this->buildRecalculateForm($sale);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->get('ekyna_commerce.sale_updater')->recalculate($sale)) {
                $event = $this->getOperator()->createResourceEvent($sale);
                $this->getOperator()->update($event);

                // TODO Some important information to display may have changed (state, etc)

                if ($event->hasErrors()) {
                    foreach ($event->getErrors() as $error) {
                        $form->addError(new FormError($error->getMessage()));
                    }
                }
            }
        }

        return $this->buildXhrSaleViewResponse($sale, $form);
    }

    /**
     * Transform action (sale transformation).
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function transformAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not yet supported.');
        }

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sourceSale */
        $sourceSale = $context->getResource($resourceName);

        $target = $request->attributes->get('target');

        if (!TransformationTargets::isValidTargetForSale($target, $sourceSale)) {
            throw new InvalidArgumentException('Invalid target.');
        }

        // Create the target sale
        /** @var \Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface $targetRepository */
        $targetRepository = $this->get('ekyna_commerce.' . $target . '.repository');
        /** @var SaleInterface $targetSale */
        $targetSale = $targetRepository->createNew();

        // Initialize the transformation
        $transformer = $this->get('ekyna_commerce.sale_transformer');
        $transformer->initialize($sourceSale, $targetSale);

        $form = $this->createTransformConfirmForm($sourceSale, $targetSale, $target);

        $form->handleRequest($request);

        // If user confirmed
        if ($form->isSubmitted() && $form->isValid()) {
            // Do sale transformation
            if (null === $event = $transformer->transform()) {
                // Redirect to target sale
                return $this->redirect($this->generateResourcePath($targetSale));
            }

            $event->toFlashes($this->getFlashBag());
        }

        $this->appendBreadcrumb(
            sprintf('%s_transform', $resourceName),
            'ekyna_core.button.transform'
        );

        return $this->render(
            $this->config->getTemplate('transform.html'),
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * Notify action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function notifyAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not yet supported.');
        }

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        $notification = $this
            ->get('ekyna_commerce.notification.builder')
            ->createNotificationFromSale($sale);

        $form = $this->createNotifyForm($sale, $notification);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sent = $this
                ->get('ekyna_commerce.mailer')
                ->sendNotification($notification, $sale);

            $this->addFlash(
                $this->getTranslator()->transChoice('ekyna_commerce.notification.message.sent', $sent),
                0 < $sent ? 'success' : 'warning'
            );

            return $this->redirect($this->generateResourcePath($sale));
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
     * Document generate action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function documentGenerateAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not supported.');
        }

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        $redirect = $this->redirect($this->generateResourcePath($sale));

        $type = $request->attributes->get('type');
        $available = SaleDocumentUtil::getSaleEditableDocumentTypes($sale);
        if (!in_array($type, $available, true)) {
            $this->addFlash('ekyna_commerce.sale.message.already_exists', 'warning');

            return $redirect;
        }

        // Generate the document file
        $document = new Document();
        $document
            ->setSale($sale)
            ->setType($type);

        $this->get('ekyna_commerce.document.builder')->build($document);
        $this->get('ekyna_commerce.document.calculator')->calculate($document);

        $renderer = $this->get('ekyna_commerce.renderer_factory')->createDocumentRenderer($document);

        $path = $renderer->create(RendererInterface::FORMAT_PDF);

        // Fake uploaded file
        $file = new UploadedFile($path, $renderer->getFilename(), null, null, null, true);

        // Attachment
        $attachment = $this
            ->get('ekyna_commerce.sale_factory')
            ->createAttachmentForSale($sale);

        $attachment
            ->setType($type)
            ->setTitle($this->getTranslator()->trans('ekyna_commerce.document.type.' . $type))
            ->setFile($file);

        $sale->addAttachment($attachment);

        // Persistence
        $config = $this
            ->get('ekyna_resource.configuration_registry')
            ->findConfiguration($attachment);

        /** @var \Ekyna\Component\Resource\Operator\ResourceOperatorInterface $operator */
        $operator = $this->get($config->getServiceKey('operator'));

        $event = $operator->persist($attachment);
        $event->toFlashes($this->getFlashBag());

        return $redirect;
    }

    /**
     * Document render action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function documentRenderAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Not supported.');
        }

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        $type = $request->attributes->get('type');
        $available = SaleDocumentUtil::getSaleEditableDocumentTypes($sale);
        if (!in_array($type, $available, true)) {
            throw $this->createNotFoundException('Unsuppoerted type');
        }

        $document = new Document();
        $document
            ->setSale($sale)
            ->setType($type);

        $this->get('ekyna_commerce.document.builder')->build($document);
        $this->get('ekyna_commerce.document.calculator')->calculate($document);

        $renderer = $this->get('ekyna_commerce.renderer_factory')->createDocumentRenderer($document);

        return $renderer->respond($request);
    }

    /**
     * Creates the transform confirm form.
     *
     * @param SaleInterface $sourceSale
     * @param SaleInterface $targetSale
     * @param string        $target
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createTransformConfirmForm(SaleInterface $sourceSale, SaleInterface $targetSale, $target)
    {
        $action = $this->generateResourcePath($sourceSale, 'transform', ['target' => $target]);

        $translator = $this->getTranslator();
        $message = $translator->trans('ekyna_commerce.sale.confirm.transform', [
            '%target%' => $translator->trans('ekyna_commerce.' . $target . '.label.singular'),
        ]);

        return $this
            ->createForm(SaleTransformType::class, $targetSale, [
                'action'            => $action,
                'attr'              => ['class' => 'form-horizontal'],
                'method'            => 'POST',
                'admin_mode'        => true,
                '_redirect_enabled' => true,
                'message'           => $message,
            ])
            ->add('actions', FormActionsType::class, [
                'buttons' => [
                    'remove' => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'warning',
                            'label'        => 'ekyna_core.button.transform',
                            'attr'         => ['icon' => 'ok'],
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
                                'href'  => $this->generateResourcePath($sourceSale),
                            ],
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Creates the notify form.
     *
     * @param SaleInterface $sale
     * @param Notification  $notification
     * @param bool          $footer
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createNotifyForm(SaleInterface $sale, Notification $notification, $footer = true)
    {
        $action = $this->generateResourcePath($sale, 'notify');

        $form = $this->createForm(NotificationType::class, $notification, [
            'sale'              => $sale,
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
                                'href'  => $this->generateResourcePath($sale),
                            ],
                        ],
                    ],
                ],
            ]);
        }

        return $form;
    }
}
