<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\CommerceBundle\Event\SaleItemModalEvent;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemConfigureType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SaleItemController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemController extends AbstractSaleController
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
     * Creates a sale item based on the subject choice.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function addAction(Request $request)
    {
        $this->isGranted('CREATE');

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        $parentConfig = $this->getParentConfiguration();

        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($parentConfig->getResourceName());

        $isXhr = $request->isXmlHttpRequest();

        $item = $this
            ->get('ekyna_commerce.sale_factory')
            ->createItemForSale($sale);

        $context->addResource($resourceName, $item);

        $flow = $this->get('ekyna_commerce.sale_item_create.form_flow');
        $flow->setGenericFormOptions([
            'action'            => $this->generateResourcePath($item, 'add', $context->getIdentifiers()),
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
                $this->getSaleHelper()->addItem($sale, $item);

                // TODO use ResourceManager
                /** @var \Ekyna\Component\Resource\Operator\ResourceOperatorInterface $saleOperator */
                $saleOperator = $this->get($parentConfig->getServiceKey('operator'));
                $event = $saleOperator->update($sale);

                if (!$isXhr) {
                    $event->toFlashes($this->getFlashBag());
                }

                if (!$event->hasErrors()) {
                    $flow->reset();

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

                return $this->redirect($this->generateResourcePath($sale));
            }
        }

        if ($isXhr) {
            $modal = $this->createModal('new', 'ekyna_commerce.sale.header.item.add');
            $modal
                ->setCondensed(true)
                ->setButtons([])
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars([
                    'flow'          => $flow,
                    'form_template' => 'EkynaCommerceBundle:Admin/Common/Item:_flow.html.twig',
                ]));

            $this->get('event_dispatcher')->dispatch(
                SaleItemModalEvent::EVENT_ADD,
                new SaleItemModalEvent($modal, $item)
            );

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_add', $resourceName),
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

    /**
     * @inheritdoc
     */
    public function newAction(Request $request)
    {
        $this->isGranted('CREATE');

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();
        $parentConfig = $this->getParentConfiguration();

        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($parentConfig->getResourceName());

        $isXhr = $request->isXmlHttpRequest();

        $item = $this
            ->get('ekyna_commerce.sale_factory')
            ->createItemForSale($sale);

        $context->addResource($resourceName, $item);

        $this->getOperator()->initialize($item);

        $form = $this->createNewResourceForm($context, !$isXhr, [
            'action'   => $this->generateResourcePath($item, 'new', $context->getIdentifiers()),
            'currency' => $sale->getCurrency()->getCode(),
            'attr'     => [
                'class' => 'form-horizontal',
            ],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO validation
            $this->getSaleHelper()->addItem($sale, $item);

            // TODO use ResourceManager
            /** @var \Ekyna\Component\Resource\Operator\ResourceOperatorInterface $saleOperator */
            $saleOperator = $this->get($parentConfig->getServiceKey('operator'));
            $event = $saleOperator->update($sale);

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

        if ($isXhr) {
            $modal = $this->createModal('new', 'ekyna_commerce.sale.header.item.new');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars());

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_new', $resourceName),
            'ekyna_commerce.sale.button.item.new'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Item:new.html.twig',
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * (Re)Configures the sale item based on the subject.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function configureAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
        $item = $context->getResource($resourceName);

        $saleName = $this->getParentConfiguration()->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($saleName);

        $this->isGranted('EDIT', $item);

        if ($item->isImmutable() || !$item->isConfigurable()) {
            throw new NotFoundHttpException('Item not found.');
        }

        $isXhr = $request->isXmlHttpRequest();

        $action = $this->generateUrl($this->getConfiguration()->getRoute('configure'), [
            $saleName . 'Id'     => $sale->getId(),
            $saleName . 'ItemId' => $item->getId(),
        ]);

        $form = $this->createForm(SaleItemConfigureType::class, $item, [
            'method'     => 'post',
            'action'     => $action,
            'admin_mode' => true,
            'attr'       => [
                'class' => 'form-horizontal',
            ],
        ]);
        if (!$isXhr) {
            $this->createFormFooter($form, $context);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->update($item);
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

        if ($isXhr) {
            $modal = $this->createModal('new', 'ekyna_commerce.sale.header.item.configure');
            $modal
                ->setContent($form->createView())
                ->setCondensed(true);

            $this->get('event_dispatcher')->dispatch(
                SaleItemModalEvent::EVENT_CONFIGURE,
                new SaleItemModalEvent($modal, $item)
            );

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_configure', $resourceName),
            'ekyna_commerce.sale.button.item.configure'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Item:configure.html.twig',
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    public function editAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
        $item = $context->getResource($resourceName);

        $saleName = $this->getParentConfiguration()->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($saleName);

        /*if ($item->isImmutable()) {
            throw new NotFoundHttpException('Item is immutable.');
        }
        if ($item->hasSubjectIdentity()) {
            throw new NotFoundHttpException('Item has identity.');
        }*/

        $this->isGranted('EDIT', $item);

        $isXhr = $request->isXmlHttpRequest();

        $action = $this->generateUrl($this->getConfiguration()->getRoute('edit'), [
            $saleName . 'Id'     => $sale->getId(),
            $saleName . 'ItemId' => $item->getId(),
        ]);

        $form = $this->createEditResourceForm($context, !$isXhr, [
            'currency' => $sale->getCurrency()->getCode(),
            'attr'     => [
                'class' => 'form-horizontal',
            ],
            'action'   => $action,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            $event = $this->getOperator()->update($item);
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

        if ($isXhr) {
            $modal = $this->createModal('new', 'ekyna_commerce.sale.header.item.edit');
            $modal
                ->setContent($form->createView())
                ->setVars($context->getTemplateVars());

            return $this->get('ekyna_core.modal')->render($modal);
        }

        $this->appendBreadcrumb(
            sprintf('%s_edit', $resourceName),
            'ekyna_commerce.sale.button.item.edit'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Item:edit.html.twig',
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    public function removeAction(Request $request)
    {
        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
        $item = $context->getResource($resourceName);

        $saleName = $this->getParentConfiguration()->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($saleName);

        if ($item->isImmutable()) {
            throw new NotFoundHttpException('Item is immutable.');
        }

        $this->isGranted('DELETE', $sale);

        $isXhr = $request->isXmlHttpRequest();

        // TODO confirmation form

        //if ($this->getSaleHelper()->removeItemById($sale, $item->getId())) {
        // TODO use ResourceManager
        $event = $this->getOperator()->delete($item);
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
            'ekyna_commerce.sale.button.item.remove'
        );

        return $this->render(
            'EkynaCommerceBundle:Admin/Common/Item:remove.html.twig',
            $context->getTemplateVars([
                // TODO 'form' => $form->createView(),
            ])
        );
    }
}
