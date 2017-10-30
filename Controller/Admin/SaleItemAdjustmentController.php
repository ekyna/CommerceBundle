<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SaleItemAdjustmentController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemAdjustmentController extends AbstractSaleController
{
    /**
     * {@inheritdoc}
     */
    public function homeAction()
    {
        throw new NotFoundHttpException();
    }

    /**
     * {@inheritdoc}
     */
    public function listAction(Request $request)
    {
        throw new NotFoundHttpException();
    }

    /**
     * {@inheritdoc}
     */
    public function showAction(Request $request)
    {
        throw new NotFoundHttpException();
    }

    /**
     * {@inheritdoc}
     */
    public function newAction(Request $request)
    {
        $this->isGranted('CREATE');

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        /** @var \Ekyna\Component\Resource\Configuration\ConfigurationInterface $saleConfig */
        $saleConfig = $this->get($this->getParentConfiguration()->getParentConfigurationId());
        $itemConfig = $this->getParentConfiguration();

        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
        $item = $context->getResource($itemConfig->getResourceName());

        $isXhr = $request->isXmlHttpRequest();

        $adjustment = $this
            ->get('ekyna_commerce.sale_factory')
            ->createAdjustmentForItem($item);
        $item->addAdjustment($adjustment); // So that we can access to the sale from the adjustment.

        $context->addResource($resourceName, $adjustment);

        $this->getOperator()->initialize($adjustment);

        $action = $this->generateUrl($this->getConfiguration()->getRoute('new'), [
            $saleConfig->getResourceName() . 'Id' => $item->getSale()->getId(),
            $itemConfig->getResourceName() . 'Id' => $item->getId(),
        ]);

        $form = $this->createNewResourceForm($context, !$isXhr, [
            'action' => $action,
            'attr'   => [
                'class' => 'form-horizontal',
            ],
            'types'  => [
                AdjustmentTypes::TYPE_DISCOUNT,
            ],
            'modes'  => [
                AdjustmentModes::MODE_PERCENT,
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->create($adjustment);
            if (!$isXhr) {
                $event->toFlashes($this->getFlashBag());
            }

            if (!$event->hasErrors()) {
                if ($isXhr) {
                    return $this->buildXhrSaleViewResponse($item->getSale());
                }

                return $this->redirect($this->generateResourcePath($item));
            } elseif ($isXhr) {
                // TODO all event messages should be bound to XHR response
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }
        }

        if ($isXhr) {
            $modal = $this->createModal('new', 'ekyna_commerce.sale.header.item_adjustment.new');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars());

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_new', $resourceName),
            'ekyna_commerce.sale.button.item_adjustment.new'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Adjustment:new.html.twig',
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function editAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        /** @var \Ekyna\Component\Resource\Configuration\ConfigurationInterface $saleConfig */
        $saleConfig = $this->get($this->getParentConfiguration()->getParentConfigurationId());
        $itemConfig = $this->getParentConfiguration();

        /** @var \Ekyna\Component\Commerce\Common\Model\AdjustmentInterface $adjustment */
        $adjustment = $context->getResource($resourceName);
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
        $item = $context->getResource($itemConfig->getResourceName());

        if ($adjustment->isImmutable()) {
            throw new NotFoundHttpException('Adjustment is immutable.');
        }

        $this->isGranted('EDIT', $adjustment);

        $isXhr = $request->isXmlHttpRequest();

        $action = $this->generateUrl($this->config->getRoute('edit'), [
            $saleConfig->getResourceName() . 'Id' => $item->getSale()->getId(),
            $itemConfig->getResourceName() . 'Id' => $item->getId(),
            $resourceName . 'Id'                  => $adjustment->getId(),
        ]);

        $form = $this->createEditResourceForm($context, !$isXhr, [
            'action' => $action,
            'attr'   => [
                'class' => 'form-horizontal',
            ],
            'types'  => [
                AdjustmentTypes::TYPE_DISCOUNT,
            ],
            'modes'  => [
                AdjustmentModes::MODE_PERCENT,
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->update($adjustment);
            if (!$isXhr) {
                $event->toFlashes($this->getFlashBag());
            }

            if (!$event->hasErrors()) {
                if ($isXhr) {
                    return $this->buildXhrSaleViewResponse($item->getSale());
                }

                return $this->redirect($this->generateResourcePath($item->getSale()));
            } elseif ($isXhr) {
                // TODO all event messages should be bound to XHR response
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }
        }

        if ($isXhr) {
            $modal = $this->createModal('new', 'ekyna_commerce.sale.header.item_adjustment.edit');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars());

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_configure', $resourceName),
            'ekyna_commerce.sale.button.item_adjustment.edit'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Adjustment:edit.html.twig',
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function removeAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        /** @var \Ekyna\Component\Commerce\Common\Model\AdjustmentInterface $adjustment */
        $adjustment = $context->getResource($resourceName);
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
        $item = $context->getResource($this->getParentConfiguration()->getResourceName());
        $sale = $item->getSale();

        if ($adjustment->isImmutable()) {
            throw new NotFoundHttpException('Adjustment is immutable.');
        }

        $this->isGranted('DELETE', $adjustment);

        $isXhr = $request->isXmlHttpRequest();

        // TODO confirmation form

        //if ($this->getSaleHelper()->removeSaleAdjustmentById($sale, $adjustment->getId())) {
        // TODO use ResourceManager
        $event = $this->getOperator()->delete($adjustment);
        if (!$isXhr) {
            $event->toFlashes($this->getFlashBag());
        }

        if (!$event->hasErrors()) {
            if ($isXhr) {
                return $this->buildXhrSaleViewResponse($sale);
            }

            return $this->redirect($this->generateResourcePath($sale));
        } /*elseif ($isXhr) {
            // TODO all event messages should be bound to XHR response
            foreach ($event->getErrors() as $error) {
                $form->addError(new FormError($error->getMessage()));
            }
        }*/
        //}

        if ($isXhr) {
            return $this->buildXhrSaleViewResponse($sale);
        }

        $this->appendBreadcrumb(
            sprintf('%s_remove', $resourceName),
            'ekyna_commerce.sale.button.item_adjustment.remove'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Adjustment:remove.html.twig',
            $context->getTemplateVars([
                // TODO 'form' => $form->createView(),
            ])
        );
    }
}
