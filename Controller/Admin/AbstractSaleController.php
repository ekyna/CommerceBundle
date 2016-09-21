<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Context;
use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\OrderAdjustmentType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\OrderItemType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemSubjectType;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AbstractSaleController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleController extends ResourceController
{
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

        $data['sale_view'] = $this->buildSaleView($sale);

        return null;
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
        if ($form->isValid()) {
            $event = $this->getOperator()->update($sale);

            if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }
        }

        return $this->buildXhrSaleViewResponse($sale, $form);
    }

    /**
     * Creates a sale item based on the subject choice.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function itemAddAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        $this->isGranted('EDIT', $sale);

        $isXhr = $request->isXmlHttpRequest();

        $item = $this
            ->get('ekyna_commerce.sale_factory')
            ->createItemForSale($sale);
        $sale->addItem($item); // So that we can access to the sale from the item.

        $action = $this->generateUrl('ekyna_commerce_order_admin_item_add', [ // TODO parameter
            'orderId' => $sale->getId(),
        ]);

        $flow = $this->get('ekyna_commerce.add_item.form_flow');
        $flow->setGenericFormOptions([
            'action'            => $action,
            'method'            => 'POST',
            'attr'              => ['class' => 'form-horizontal'],
            'admin_mode'        => true,
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
                $event = $this->getOperator()->update($sale);

                if ($event->hasErrors()) {
                    foreach ($event->getErrors() as $error) {
                        $form->addError(new FormError($error->getMessage()));
                    }
                } else {
                    $flow->reset(); // remove step data from the session
                }

                if ($isXhr) {
                    // We need to refresh the sale to get proper "id indexed" collections.
                    // TODO move to resource listener : refresh all collections indexed by "id"
                    $this->getOperator()->refresh($sale);

                    return $this->buildXhrSaleViewResponse($sale);
                } else {
                    $event->toFlashes($this->getFlashBag());
                }

                return $this->redirect($this->generateResourcePath($sale));
            }
        }

        if ($isXhr) {
            $modal = $this->createModal('new', 'ekyna_commerce.sale.header.item.add');
            $modal
                ->setButtons([])
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars([
                    //'form'          => $form->createView(),
                    'flow'          => $flow,
                    'form_template' => 'EkynaCommerceBundle:Admin/Common/Item:_flow.html.twig',
                ]));

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_item_add', 'ekyna_commerce_order_item'),
            'ekyna_commerce.sale.button.item.add'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Item:add.html.twig',
            $context->getTemplateVars([
                'form' => $form->createView(),
                'flow' => $flow,
            ])
        );
    }

    public function itemConfigureAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        $this->isGranted('EDIT', $sale);

        $isXhr = $request->isXmlHttpRequest();

        $saleHelper = $this->get('ekyna_commerce.sale_helper');

        $itemId = intval($request->attributes->get('itemId'));
        if (0 >= $itemId) {
            throw new NotFoundHttpException('Unexpected item identifier.');
        }
        $item = $saleHelper->findItemById($sale, intval($itemId));
        if (null === $item || $item->isImmutable() || !$item->isConfigurable()) {
            throw new NotFoundHttpException('Item not found.');
        }

        $form = $this->createForm(SaleItemSubjectType::class, $item, [
            'method' => 'post',
            'action' => $this->generateUrl('ekyna_commerce_order_admin_item_configure', [
                'orderId' => $sale->getId(),
                'itemId'  => $item->getId(),
            ]),
            'attr'   => [
                'class' => 'form-horizontal',
            ],
        ]);

        if (!$isXhr) {
            $form->add('submit', SubmitType::class, [
                'label' => 'ekyna_core.button.validate',
            ]);
            // TODO cancel button
        }

        $form->handleRequest($request);
        if ($form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->update($sale);

            if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }

            if ($isXhr) {
                return $this->buildXhrSaleViewResponse($sale);
            } else {
                $event->toFlashes($this->getFlashBag());
            }

            return $this->redirect($this->generateResourcePath($sale));
        }

        if ($isXhr) {
            $modal = $this->createModal('new', 'ekyna_commerce.sale.header.item.configure');
            $modal
                ->setContent($form->createView())
                ->setVars([
                    'form_template' => 'EkynaCommerceBundle:Form:sale_item_subject_form.html.twig',
                ]);

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_item_configure', 'ekyna_commerce_order'),
            'ekyna_commerce.sale.button.item.configure'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Item:configure.html.twig',
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    public function itemNewAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        $this->isGranted('EDIT', $sale);

        $isXhr = $request->isXmlHttpRequest();

        $item = $this
            ->get('ekyna_commerce.sale_factory')
            ->createItemForSale($sale);
        $sale->addItem($item); // So that we can access to the sale from the item.

        $form = $this
            ->createForm(OrderItemType::class, $item, [
                'method' => 'post',
                'action' => $this->generateUrl('ekyna_commerce_order_admin_item_new', [
                    'orderId' => $sale->getId(),
                ]),
                'attr'   => [
                    'class' => 'form-horizontal',
                ],
            ]);

        if (!$isXhr) {
            $form->add('submit', SubmitType::class, [
                'label' => 'ekyna_core.button.validate',
            ]);
            // TODO cancel button
        }

        $form->handleRequest($request);
        if ($form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->update($sale);

            if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }

            if ($isXhr) {
                // We need to refresh the sale to get proper "id indexed" collections.
                // TODO move to resource listener : refresh all collections indexed by "id"
                $this->getOperator()->refresh($sale);

                return $this->buildXhrSaleViewResponse($sale);
            } else {
                $event->toFlashes($this->getFlashBag());
            }

            return $this->redirect($this->generateResourcePath($sale));
        }

        if ($isXhr) {
            $modal = $this->createModal('new', 'ekyna_commerce.sale.header.item.new');
            $modal
                ->setContent($form->createView())
                ->setVars([
                    'form_template' => 'EkynaCommerceBundle:Admin/Common/Item:_form.html.twig',
                ]);

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_item_new', 'ekyna_commerce_order'),
            'ekyna_commerce.sale.button.item.new'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Item:new.html.twig',
            $context->getTemplateVars([
                'form'          => $form->createView(),
                'form_template' => 'EkynaCommerceBundle:Admin/Common/Item:_form.html.twig',
            ])
        );
    }

    public function itemEditAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        $this->isGranted('EDIT', $sale);

        $isXhr = $request->isXmlHttpRequest();

        $saleHelper = $this->get('ekyna_commerce.sale_helper');

        $itemId = intval($request->attributes->get('itemId'));
        if (0 >= $itemId) {
            throw new NotFoundHttpException('Unexpected item identifier.');
        }
        $item = $saleHelper->findItemById($sale, $itemId);
        if (null === $item || $item->isImmutable()) {
            throw new NotFoundHttpException('Item not found.');
        }

        $form = $this
            ->createForm(OrderItemType::class, $item, [
                'method' => 'post',
                'action' => $this->generateUrl('ekyna_commerce_order_admin_item_edit', [
                    'orderId' => $sale->getId(),
                    'itemId'  => $item->getId(),
                ]),
                'attr'   => [
                    'class' => 'form-horizontal',
                ],
            ]);

        if (!$isXhr) {
            $form->add('submit', SubmitType::class, [
                'label' => 'ekyna_core.button.validate',
            ]);
            // TODO cancel button
        }

        $form->handleRequest($request);
        if ($form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->update($sale);

            if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }

            if ($isXhr) {
                return $this->buildXhrSaleViewResponse($sale);
            } else {
                $event->toFlashes($this->getFlashBag());
            }

            return $this->redirect($this->generateResourcePath($sale));
        }

        if ($isXhr) {
            $modal = $this->createModal('new', 'ekyna_commerce.sale.header.item.edit');
            $modal
                ->setContent($form->createView())
                ->setVars([
                    'form_template' => 'EkynaCommerceBundle:Admin/Common/Item:_form.html.twig',
                ]);

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_item_configure', 'ekyna_commerce_order'),
            'ekyna_commerce.sale.button.item.edit'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Item:edit.html.twig',
            $context->getTemplateVars([
                'form'          => $form->createView(),
                'form_template' => 'EkynaCommerceBundle:Admin/Common/Item:_form.html.twig',
            ])
        );
    }

    public function itemRemoveAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        $this->isGranted('EDIT', $sale);

        $isXhr = $request->isXmlHttpRequest();

        $saleHelper = $this->get('ekyna_commerce.sale_helper');

        $itemId = intval($request->attributes->get('itemId'));
        if (0 >= $itemId) {
            throw new NotFoundHttpException('Unexpected item identifier.');
        }

        // TODO confirmation form

        if ($saleHelper->removeItemById($sale, $itemId)) {
            // TODO use ResourceManager
            $event = $this->getOperator()->update($sale);

            /* TODO if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }*/

            if ($isXhr) {
                return $this->buildXhrSaleViewResponse($sale);
            } else {
                $event->toFlashes($this->getFlashBag());
            }

            return $this->redirect($this->generateResourcePath($sale));
        } else {
            // TODO Warn about immutable item ?
        }

        if ($isXhr) {
            return $this->buildXhrSaleViewResponse($sale);
        }

        $this->appendBreadcrumb(
            sprintf('%s_item_remove', 'ekyna_commerce_order'),
            'ekyna_commerce.sale.button.item.remove'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Item:remove.html.twig',
            $context->getTemplateVars([
                // TODO 'form' => $form->createView(),
            ])
        );
    }

    public function adjustmentNewAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        $this->isGranted('EDIT', $sale);

        $isXhr = $request->isXmlHttpRequest();

        $adjustment = $this
            ->get('ekyna_commerce.sale_factory')
            ->createAdjustmentForSale($sale);
        $sale->addAdjustment($adjustment); // So that we can access to the sale from the adjustment.

        $form = $this
            ->createForm(OrderAdjustmentType::class, $adjustment, [
                'method' => 'post',
                'action' => $this->generateUrl('ekyna_commerce_order_admin_adjustment_new', [
                    'orderId' => $sale->getId(),
                ]),
                'attr'   => [
                    'class' => 'form-horizontal',
                ],
            ]);

        if (!$isXhr) {
            $form->add('submit', SubmitType::class, [
                'label' => 'ekyna_core.button.validate',
            ]);
            // TODO cancel button
        }

        $form->handleRequest($request);
        if ($form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->update($sale);

            if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }

            if ($isXhr) {
                // We need to refresh the sale to get proper "id indexed" collections.
                // TODO move to resource listener : refresh all collections indexed by "id"
                $this->getOperator()->refresh($sale);

                return $this->buildXhrSaleViewResponse($sale);
            } else {
                $event->toFlashes($this->getFlashBag());
            }

            return $this->redirect($this->generateResourcePath($sale));
        }

        if ($isXhr) {
            $modal = $this->createModal('new', 'ekyna_commerce.sale.header.adjustment.new');
            $modal
                ->setContent($form->createView())
                ->setVars([
                    'form_template' => 'EkynaCommerceBundle:Admin/Common/Adjustment:_form.html.twig',
                ]);

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_adjustment_new', 'ekyna_commerce_order'),
            'ekyna_commerce.sale.button.adjustment.new'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Adjustment:new.html.twig',
            $context->getTemplateVars([
                'form'          => $form->createView(),
                'form_template' => 'EkynaCommerceBundle:Admin/Common/Adjustment:_form.html.twig',
            ])
        );
    }

    public function adjustmentEditAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        $this->isGranted('EDIT', $sale);

        $isXhr = $request->isXmlHttpRequest();

        $saleHelper = $this->get('ekyna_commerce.sale_helper');

        $adjustmentId = intval($request->attributes->get('adjustmentId'));
        if (0 >= $adjustmentId) {
            throw new NotFoundHttpException('Unexpected adjustment identifier.');
        }
        $adjustment = $saleHelper->findSaleAdjustmentById($sale, $adjustmentId);
        if (null === $adjustment || $adjustment->isImmutable()) {
            throw new NotFoundHttpException('Adjustment not found.');
        }

        $form = $this
            ->createForm(OrderAdjustmentType::class, $adjustment, [
                'method' => 'post',
                'action' => $this->generateUrl('ekyna_commerce_order_admin_adjustment_edit', [
                    'orderId'      => $sale->getId(),
                    'adjustmentId' => $adjustment->getId(),
                ]),
                'attr'   => [
                    'class' => 'form-horizontal',
                ],
            ]);

        if (!$isXhr) {
            $form->add('submit', SubmitType::class, [
                'label' => 'ekyna_core.button.validate',
            ]);
            // TODO cancel button
        }

        $form->handleRequest($request);
        if ($form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->update($sale);

            if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }

            if ($isXhr) {
                return $this->buildXhrSaleViewResponse($sale);
            } else {
                $event->toFlashes($this->getFlashBag());
            }

            return $this->redirect($this->generateResourcePath($sale));
        }

        if ($isXhr) {
            $modal = $this->createModal('new', 'ekyna_commerce.sale.header.adjustment.edit');
            $modal
                ->setContent($form->createView())
                ->setVars([
                    'form_template' => 'EkynaCommerceBundle:Admin/Common/Adjustment:_form.html.twig',
                ]);

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_adjustment_configure', 'ekyna_commerce_order'),
            'ekyna_commerce.sale.button.adjustment.edit'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Adjustment:edit.html.twig',
            $context->getTemplateVars([
                'form'          => $form->createView(),
                'form_template' => 'EkynaCommerceBundle:Admin/Common/Adjustment:_form.html.twig',
            ])
        );
    }

    public function adjustmentRemoveAction(Request $request)
    {
        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($resourceName);

        $this->isGranted('EDIT', $sale);

        $isXhr = $request->isXmlHttpRequest();

        $saleHelper = $this->get('ekyna_commerce.sale_helper');

        $adjustmentId = intval($request->attributes->get('adjustmentId'));
        if (0 >= $adjustmentId) {
            throw new NotFoundHttpException('Unexpected adjustment identifier.');
        }

        // TODO confirmation form

        if ($saleHelper->removeSaleAdjustmentById($sale, $adjustmentId)) {
            // TODO use ResourceManager
            $event = $this->getOperator()->update($sale);

            /* TODO if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }*/

            if ($isXhr) {
                return $this->buildXhrSaleViewResponse($sale);
            } else {
                $event->toFlashes($this->getFlashBag());
            }

            return $this->redirect($this->generateResourcePath($sale));
        } else {
            // TODO Warn about immutable adjustment ?
        }

        if ($isXhr) {
            return $this->buildXhrSaleViewResponse($sale);
        }

        $this->appendBreadcrumb(
            sprintf('%s_adjustment_remove', 'ekyna_commerce_order'),
            'ekyna_commerce.sale.button.adjustment.remove'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Adjustment:remove.html.twig',
            $context->getTemplateVars([
                // TODO 'form' => $form->createView(),
            ])
        );
    }

    /**
     * Builds the recalculate form.
     *
     * @param SaleInterface $sale
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function buildRecalculateForm(SaleInterface $sale)
    {
        return $this->getSaleHelper()->createQuantitiesForm($sale, [
            'method' => 'post',
            'action' => $this->generateUrl('ekyna_commerce_order_admin_recalculate', [
                'orderId' => $sale->getId(),
            ]),
        ]);
    }

    /**
     * Builds the sale view.
     *
     * @param SaleInterface $sale
     * @param FormInterface $form The recalculate form
     *
     * @return \Ekyna\Component\Commerce\Common\View\SaleView
     */
    protected function buildSaleView(SaleInterface $sale, FormInterface $form = null)
    {
        if (null === $form) {
            $form = $this->buildRecalculateForm($sale);
        }

        $view = $this->getSaleHelper()->buildView($sale, [
            'private'      => true,
            'editable'     => true,
            'vars_builder' => $this->get('ekyna_commerce.order.view_vars_builder'),
        ]);
        $view->vars['form'] = $form->createView();

        return $view;
    }

    /**
     * Returns the XHR cart view response.
     *
     * @param SaleInterface $sale
     * @param FormInterface $form The recalculate form
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function buildXhrSaleViewResponse(SaleInterface $sale, FormInterface $form = null)
    {
        $response = $this->render('EkynaCommerceBundle:Common:response.xml.twig', [
            'sale_view' => $this->buildSaleView($sale, $form),
        ]);

        $response->headers->set('Content-type', 'application/xml');

        return $response;
    }

    /**
     * Returns the sale helper.
     *
     * @return \Ekyna\Bundle\CommerceBundle\Service\SaleHelper|object
     */
    protected function getSaleHelper()
    {
        return $this->get('ekyna_commerce.sale_helper');
    }
}
