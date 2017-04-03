<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleShipmentType;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Model\CartStates;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\TransformationTargets;
use Ekyna\Component\Commerce\Common\Util\AddressUtil;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteStates;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints;

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

            if (null !== $address = $customer->getDefaultInvoiceAddress()) {
                $invoiceAddress = $this->get('ekyna_commerce.sale_factory')->createAddressForSale($sale);
                AddressUtil::copy($address, $invoiceAddress);
                $sale->setInvoiceAddress($invoiceAddress);
            }

            if (null !== $address = $customer->getDefaultDeliveryAddress()) {
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
    protected function buildShowData(
        /** @noinspection PhpUnusedParameterInspection */
        array &$data,
        /** @noinspection PhpUnusedParameterInspection */
        Context $context
    ) {
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

    public function invoiceAction(Request $request)
    {
        $context = $this->loadContext($request);

        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource();

        $this->isGranted('VIEW', $sale);

        $response = new Response();
        if (!$this->getParameter('kernel.debug')) {
            $response->setLastModified($sale->getUpdatedAt());
            if ($response->isNotModified($request)) {
                return $response;
            }
        }

        $view = $this->getSaleHelper()->buildView($sale, [
            'private'      => false,
            'editable'     => false,
        ]);

        $content = $this->renderView('EkynaCommerceBundle:Document:invoice.html.twig', [
            'sale' => $sale,
            'view' => $view,
        ]);

        $format = $request->attributes->get('_format', 'html');
        if ('html' === $format) {
            $response->setContent($content);
        } elseif ('pdf' === $format) {
            $response->setContent($this->get('knp_snappy.pdf')->getOutputFromHtml($content));
            $response->headers->add(['Content-Type' => 'application/pdf']);
        } else {
            throw new NotFoundHttpException('Unsupported format.');
        }

        if ($request->query->get('_download', false)) {
            $filename = sprintf('order-%s.%s', $sale->getNumber(), $format);
            $contentDisposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename
            );
            $response->headers->set('Content-Disposition', $contentDisposition);
        }

        return $response;
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
            throw new NotFoundHttpException('Transformation through XHR.');
        }

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sourceSale */
        $sourceSale = $context->getResource($resourceName);

        $target = $request->attributes->get('target');

        if (!TransformationTargets::isValidTargetForSale($target, $sourceSale)) {
            throw new InvalidArgumentException('Invalid target.');
        }

        $form = $this->createTransformConfirmForm($sourceSale, $target);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface $targetRepository */
            $targetRepository = $this->get('ekyna_commerce.' . $target . '.repository');

            // Create the target sale
            /** @var SaleInterface $targetSale */
            $targetSale = $targetRepository->createNew();

            // Copy from source sale to target sale
            $this->get('ekyna_commerce.sale_transformer')
                ->copySale($sourceSale, $targetSale);

            /** @var \Ekyna\Component\Resource\Operator\ResourceOperatorInterface $targetOperator */
            $targetOperator = $this->get('ekyna_commerce.' . $target . '.operator');

            // Persist the target sale
            $targetEvent = $targetOperator->persist($targetSale);
            if ($targetEvent->isPropagationStopped() || $targetEvent->hasErrors()) {
                $targetEvent->toFlashes($this->getFlashBag());
            } else {
                // Disable the uploadable listener so that files won't be removed.
                $uploadableListener = $this->get('ekyna_commerce.common.uploadable_listener');
                $uploadableListener->setEnabled(false);

                // Delete the source sale
                $sourceEvent = $this->getOperator()->delete($sourceSale, true); // Hard delete
                if ($sourceEvent->isPropagationStopped() || $sourceEvent->hasErrors()) {
                    $sourceEvent->toFlashes($this->getFlashBag());
                } else {
                    // Redirect to target sale
                    return $this->redirect($this->generateResourcePath($targetSale));
                }

                $uploadableListener->setEnabled(true);
            }
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
     * Creates the transform confirm form.
     *
     * @param SaleInterface $sale
     * @param string        $target
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createTransformConfirmForm(SaleInterface $sale, $target)
    {
        $action = $this->generateResourcePath($sale, 'transform', ['target' => $target]);

        $translator = $this->getTranslator();
        $message = $translator->trans('ekyna_commerce.sale.confirm.transform', [
            '%target%' => $translator->trans('ekyna_commerce.' . $target . '.label.singular'),
        ]);

        return $this
            ->createFormBuilder(null, [
                'action'            => $action,
                'attr'              => ['class' => 'form-horizontal'],
                'method'            => 'POST',
                'admin_mode'        => true,
                '_redirect_enabled' => true,
            ])
            ->add('confirm', Type\CheckboxType::class, [
                'label'       => $message,
                'attr'        => ['align_with_widget' => true],
                'required'    => true,
                'constraints' => [
                    new Constraints\IsTrue(),
                ],
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
                                'href'  => $this->generateResourcePath($sale),
                            ],
                        ],
                    ],
                ],
            ])
            ->getForm();
    }
}
