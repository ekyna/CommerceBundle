<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\AdminBundle\Controller\Resource\ToggleableTrait;
use Ekyna\Bundle\CommerceBundle\Form\Type\Notify\NotifyType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleShipmentType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleTransformType;
use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentGenerator;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartStates;
use Ekyna\Component\Commerce\Common\Export\SaleCsvExporter;
use Ekyna\Component\Commerce\Common\Export\SaleXlsExporter;
use Ekyna\Component\Commerce\Common\Model\NotificationTypes;
use Ekyna\Component\Commerce\Common\Model\Notify;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleSources;
use Ekyna\Component\Commerce\Common\Model\TransformationTargets;
use Ekyna\Component\Commerce\Common\Util\AddressUtil;
use Ekyna\Component\Commerce\Document\Builder\DocumentBuilder;
use Ekyna\Component\Commerce\Document\Calculator\DocumentCalculator;
use Ekyna\Component\Commerce\Document\Model\Document;
use Ekyna\Component\Commerce\Document\Util\SaleDocumentUtil;
use Ekyna\Component\Commerce\Exception\CommerceExceptionInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\PdfException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SaleController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleController extends AbstractSaleController
{
    use ToggleableTrait;

    /**
     * @inheritDoc
     */
    protected function createNew(Context $context)
    {
        /** @var SaleInterface $sale */
        $sale = parent::createNew($context);

        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $customer */
        $customer = $this
            ->get('ekyna_commerce.customer.repository')
            ->find($context->getRequest()->query->get('customer'));

        if (null !== $customer) {
            $sale->setCustomer($customer);

            if (null !== $iAddress = $customer->getDefaultInvoiceAddress(true)) {
                $invoiceAddress = $this->get('ekyna_commerce.sale_factory')->createAddressForSale($sale);
                AddressUtil::copy($iAddress, $invoiceAddress);
                $sale->setInvoiceAddress($invoiceAddress);
            }

            if (null !== $dAddress = $customer->getDefaultDeliveryAddress(true)) {
                if (null !== $iAddress && $dAddress !== $iAddress) {
                    $deliveryAddress = $this->get('ekyna_commerce.sale_factory')->createAddressForSale($sale);
                    AddressUtil::copy($dAddress, $deliveryAddress);
                    $sale
                        ->setDeliveryAddress($deliveryAddress)
                        ->setSameAddress(false);
                }
            }
        }

        return $sale;
    }

    /**
     * @inheritdoc
     */
    protected function buildShowData(array &$data, Context $context)
    {
        /** @var SaleInterface $sale */
        $sale = $context->getResource();

        if ($sale instanceof CartInterface && $sale->getState() === CartStates::STATE_ACCEPTED) {
            $this->addFlash('ekyna_commerce.cart.message.transformation_to_order_is_ready');
        } elseif ($sale instanceof QuoteInterface && $sale->getState() === QuoteStates::STATE_ACCEPTED) {
            $this->addFlash('ekyna_commerce.quote.message.transformation_to_order_is_ready');
        } elseif ($sale->canBeReleased()) {
            $this->addFlash('ekyna_commerce.order.message.can_be_released', 'warning');
        }
        $data['sale_view'] = $this->buildSaleView($sale);

        return null;
    }

    /**
     * Sale summary action.
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
        /** @var SaleInterface $sale */
        $sale = $context->getResource();

        $this->isGranted('VIEW', $sale);

        $response = new Response();
        $response->setVary(['Accept', 'Accept-Encoding']);
        $response->setExpires(new \DateTime('+3 min'));

        $html = false;
        $accept = $request->getAcceptableContentTypes();

        if (in_array('application/json', $accept, true)) {
            $response->headers->add(['Content-Type' => 'application/json']);
        } elseif (in_array('text/html', $accept, true)) {
            $html = true;
        } else {
            throw $this->createNotFoundException("Unsupported content type.");
        }

        if ($html) {
            $content = $this->get('serializer')->normalize($sale, 'json', ['groups' => ['Summary']]);
            $content = $this->renderView(
                '@EkynaCommerce/Admin/Common/Sale/summary.html.twig',
                array_replace($content, [
                    'shipment' => $sale instanceof ShipmentSubjectInterface,
                    'invoice'  => $sale instanceof InvoiceSubjectInterface && !$sale->isSample(),
                ])
            );
        } else {
            $content = $this->get('serializer')->serialize($sale, 'json', ['groups' => ['Summary']]);
        }

        $response->setContent($content);

        return $response;
    }

    /**
     * Edit the sale shipment data.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function editShipmentAction(Request $request)
    {
        $context = $this->loadContext($request);
        /** @var SaleInterface $sale */
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

        $formTemplate = '@EkynaCommerce/Admin/Common/Sale/_form_edit_shipment.html.twig';

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
            '@EkynaCommerce/Admin/Common/Sale/edit_shipment.html.twig',
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
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createShipmentEditForm(SaleInterface $sale, $footer = true)
    {
        $action = $this->generateResourcePath($sale, 'edit_shipment');

        $form = $this->createForm(SaleShipmentType::class, $sale, [
            'action'            => $action,
            'attr'              => ['class' => 'form-horizontal'],
            'method'            => 'POST',
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
     * @return Response
     */
    public function refreshAction(Request $request)
    {
        if (!($request->isXmlHttpRequest() && $request->getMethod() === 'GET')) {
            throw $this->createNotFoundException();
        }

        $context = $this->loadContext($request);
        /** @var SaleInterface $sale */
        $sale = $context->getResource();

        return $this->buildXhrSaleViewResponse($sale);
    }

    /**
     * Updates the sale's state.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function updateStateAction(Request $request)
    {
        if (!$this->getParameter('kernel.debug')) {
            throw $this->createNotFoundException();
        }

        /* TODO if (!($request->isXmlHttpRequest())) {
            throw $this->createNotFoundException();
        }*/

        $context = $this->loadContext($request);

        /** @var SaleInterface $sale */
        $sale = $context->getResource();

        $changed = $this->get('ekyna_commerce.sale_updater')->recalculate($sale);

        /** @var \Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface $resolver */
        $resolver = $this->get($this->config->getServiceKey('state_resolver'));
        $changed |= $resolver->resolve($sale);

        if ($changed) {
            $event = $this->getOperator()->update($sale);
            $event->toFlashes($this->getFlashBag());
        }

        return $this->redirect($this->generateResourcePath($sale));
        // TODO return $this->buildXhrSaleViewResponse($sale);
    }

    /**
     * Recalculate action (updates the items quantities).
     *
     * @param Request $request
     *
     * @return Response
     */
    public function recalculateAction(Request $request)
    {
        if (!($request->isXmlHttpRequest() && $request->getMethod() === 'POST')) {
            throw $this->createNotFoundException();
        }

        $context = $this->loadContext($request);
        /** @var SaleInterface $sale */
        $sale = $context->getResource();

        $form = $this->buildQuantitiesForm($sale);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('ekyna_commerce.sale_updater')->recalculate($sale);
            $event = $this->getOperator()->createResourceEvent($sale);
            $this->getOperator()->update($event);

            // TODO Some important information to display may have changed (state, etc)

            if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
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
     * @return Response
     */
    public function transformAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not yet supported.');
        }

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var SaleInterface $sourceSale */
        $sourceSale = $context->getResource($resourceName);

        $target = $request->attributes->get('target');
        if (!TransformationTargets::isValidTargetForSale($target, $sourceSale, false)) {
            throw new InvalidArgumentException('Invalid target.');
        }

        // Create the target sale
        /** @var \Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface $targetRepository */
        $targetRepository = $this->get('ekyna_commerce.' . $target . '.repository');
        /** @var SaleInterface $targetSale */
        $targetSale = $targetRepository->createNew();

        $transformer = $this->get('ekyna_commerce.sale_transformer');

        // Initialize the transformation
        $event = $transformer->initialize($sourceSale, $targetSale);
        if ($event->isPropagationStopped()) {
            if ($event->hasErrors()) {
                $event->toFlashes($this->getFlashBag());
            }

            return $this->redirect($this->generateResourcePath($sourceSale));
        }

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
                'target' => $target,
                'form'   => $form->createView(),
            ])
        );
    }

    /**
     * Duplicate action (sale copy).
     *
     * @param Request $request
     *
     * @return Response
     */
    public function duplicateAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Not yet supported.');
        }

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var SaleInterface $sourceSale */
        $sourceSale = $context->getResource($resourceName);

        /** @var SaleInterface $targetSale */
        $target = $request->attributes->get('target');
        if (!TransformationTargets::isValidTargetForSale($target, $sourceSale, true)) {
            throw new InvalidArgumentException('Invalid target.');
        }

        // Create the target sale
        /** @var \Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface $targetRepository */
        $targetRepository = $this->get('ekyna_commerce.' . $target . '.repository');
        /** @var SaleInterface $targetSale */
        $targetSale = $targetRepository->createNew();
        /** @var \Ekyna\Component\Resource\Operator\ResourceOperatorInterface $targetOperator */
        $targetOperator = $this->get('ekyna_commerce.' . $target . '.operator');
        $targetOperator->initialize($targetSale);

        // Copies source to target
        $this
            ->get('ekyna_commerce.sale_copier_factory')
            ->create($sourceSale, $targetSale)
            ->copyData()
            ->copyItems();

        $targetSale
            ->setSameAddress(true)
            ->setCustomerGroup(null)
            ->setPaymentTerm(null)
            ->setOutstandingLimit(0)
            ->setDepositTotal(0)
            ->setSource(SaleSources::SOURCE_COMMERCIAL)
            ->setExchangeRate(null)
            ->setExchangeDate(null)
            ->setAcceptedAt(null);

        $form = $this->createDuplicateConfirmForm($sourceSale, $targetSale, $target);

        $form->handleRequest($request);

        // If user confirmed
        if ($form->isSubmitted() && $form->isValid()) {
            $event = $targetOperator->create($targetSale);

            if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            } else {
                $event->toFlashes($this->getFlashBag());

                return $this->redirect($this->generateResourcePath($targetSale));
            }
        }

        $this->appendBreadcrumb(
            sprintf('%s_duplicate', $resourceName),
            'ekyna_core.button.duplicate'
        );

        return $this->render(
            $this->config->getTemplate('duplicate.html'),
            $context->getTemplateVars([
                'target' => $target,
                'form'   => $form->createView(),
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
            throw $this->createNotFoundException('Not yet supported.');
        }

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        $builder = $this->get('ekyna_commerce.notify.builder');

        $notify = $builder->create(NotificationTypes::MANUAL, $sale);

        $builder->build($notify);

        $form = $this->createNotifyForm($sale, $notify);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('ekyna_commerce.notify.queue')->add($notify);

            $this->addFlash('ekyna_commerce.notify.message.sent', 'success');

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
     * Export (single) action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function exportAction(Request $request): Response
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        $format = $request->getRequestFormat('csv');
        if ($format === 'csv') {
            $exporter = $this->get(SaleCsvExporter::class);
        } elseif($format === 'xls') {
            $exporter = $this->get(SaleXlsExporter::class);
        } else {
            throw new InvalidArgumentException("Unexpected format '$format'");
        }

        try {
            $path = $exporter->export($sale);
        } catch (CommerceExceptionInterface $e) {
            if ($this->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->addFlash($e->getMessage(), 'danger');

            return $this->redirect($this->generateResourcePath($sale));
        }

        clearstatcache(true, $path);

        $response = new BinaryFileResponse(new Stream($path));

        $disposition = $response
            ->headers
            ->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $sale->getNumber() . '.' . $format);

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'text/csv');

        return $response;
    }

    /**
     * Set exchange rate action.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function setExchangeRateAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            throw $this->createNotFoundException("Not yet supported.");
        }

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        if ($this->get('ekyna_commerce.sale_updater')->updateExchangeRate($sale, true)) {
            $event = $this->getOperator()->update($sale);
            $event->toFlashes($this->getFlashBag());
        }

        if (null !== $path = $request->query->get('_redirect')) {
            $redirect = $path;
        } elseif (null !== $referer = $request->headers->get('referer')) {
            $redirect = $referer;
        } else {
            $redirect = $this->generateResourcePath($sale);
        }

        return $this->redirect($redirect);
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
            throw $this->createNotFoundException('Not supported.');
        }

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        $redirect = $this->redirect($this->generateResourcePath($sale));

        $type = $request->attributes->get('type');

        try {
            $attachment = $this
                ->get(DocumentGenerator::class)
                ->generate($sale, $type);
        } catch (InvalidArgumentException $e) {
            $this->addFlash('ekyna_commerce.sale.message.already_exists', 'warning');

            return $redirect;
        } catch (PdfException $e) {
            $this->addFlash('ekyna_commerce.document.message.failed_to_generate', 'danger');

            return $redirect;
        }

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
            throw $this->createNotFoundException('Not supported.');
        }

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        $type = $request->attributes->get('type');
        $available = SaleDocumentUtil::getSaleEditableDocumentTypes($sale);
        if (!in_array($type, $available, true)) {
            throw $this->createNotFoundException('Unsuppoerted type');
        }

        $document = new Document();
        $document
            ->setSale($sale)
            ->setType($type)
            ->setLocale($request->query->get('locale'))
            ->setCurrency($request->query->get('currency'));

        $this->get(DocumentBuilder::class)->build($document);
        $this->get(DocumentCalculator::class)->calculate($document);

        $renderer = $this->get(RendererFactory::class)->createRenderer($document);

        try {
            return $renderer->respond($request);
        } catch (PdfException $e) {
            $this->addFlash('ekyna_commerce.document.message.failed_to_generate', 'danger');

            return $this->redirectToReferer($this->generateResourcePath($sale));
        }
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
    protected function createTransformConfirmForm(SaleInterface $sourceSale, SaleInterface $targetSale, string $target)
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
                '_redirect_enabled' => true,
                'message'           => $message,
            ])
            ->add('actions', FormActionsType::class, [
                'buttons' => [
                    'transform' => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'warning',
                            'label'        => 'ekyna_core.button.transform',
                            'attr'         => ['icon' => 'ok'],
                        ],
                    ],
                    'cancel'    => [
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
     * Creates the duplicate confirm form.
     *
     * @param SaleInterface $sourceSale
     * @param SaleInterface $targetSale
     * @param string        $target
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createDuplicateConfirmForm(SaleInterface $sourceSale, SaleInterface $targetSale, string $target)
    {
        $type = $this->get(sprintf('ekyna_commerce.%s.configuration', $target))->getFormType();

        $action = $this->generateResourcePath($sourceSale, 'duplicate', ['target' => $target]);

        $form = $this->createForm($type, $targetSale, [
            'action'            => $action,
            'method'            => 'POST',
            'attr'              => ['class' => 'form-horizontal form-with-tabs'],
            '_redirect_enabled' => true,
        ]);

        $translator = $this->getTranslator();
        $message = $translator->trans('ekyna_commerce.sale.confirm.duplicate');

        return $form
            ->add('confirm', Type\CheckboxType::class, [
                'label'       => $message,
                'attr'        => ['align_with_widget' => true],
                'mapped'      => false,
                'required'    => true,
                'constraints' => [
                    new Assert\IsTrue(),
                ],
            ])
            ->add('actions', FormActionsType::class, [
                'buttons' => [
                    'duplicate' => [
                        'type'    => Type\SubmitType::class,
                        'options' => [
                            'button_class' => 'warning',
                            'label'        => 'ekyna_core.button.duplicate',
                            'attr'         => ['icon' => 'ok'],
                        ],
                    ],
                    'cancel'    => [
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
     * @param Notify        $notification
     * @param bool          $footer
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createNotifyForm(SaleInterface $sale, Notify $notification, $footer = true)
    {
        $action = $this->generateResourcePath($sale, 'notify');

        $form = $this->createForm(NotifyType::class, $notification, [
            'source'            => $sale,
            'action'            => $action,
            'attr'              => ['class' => 'form-horizontal'],
            'method'            => 'POST',
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
