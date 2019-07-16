<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\CommerceBundle\Event\SaleItemModalEvent;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemConfigureType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleItemPrioritizeType;
use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
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
     * @inheritdoc
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

        // Set the context using sale
        $this
            ->get('ekyna_commerce.common.context_provider')
            ->setContext($sale);

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
                    'form_template' => '@EkynaCommerce/Admin/Common/Item/_flow.html.twig',
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
            '@EkynaCommerce/Admin/Common/Item/add.html.twig',
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

        // Set the context using sale
        $this
            ->get('ekyna_commerce.common.context_provider')
            ->setContext($sale);

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
            '@EkynaCommerce/Admin/Common/Item/new.html.twig',
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
     * @return \Symfony\Component\HttpFoundation\Response
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
            '@EkynaCommerce/Admin/Common/Item/configure.html.twig',
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * @inheritdoc
     */
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
            '@EkynaCommerce/Admin/Common/Item/edit.html.twig',
            $context->getTemplateVars([
                'form' => $form->createView(),
            ])
        );
    }

    /**
     * Move up the resource.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moveUpAction(Request $request)
    {
        $context = $this->loadContext($request);
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
        $item = $context->getResource($this->config->getResourceName());
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($this->getParentConfiguration()->getResourceName());

        $this->isGranted('EDIT', $item);

        if (0 < $item->getPosition()) {
            $item->setPosition($item->getPosition() - 1);

            // TODO use ResourceManager
            $this->getOperator()->update($item);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->buildXhrSaleViewResponse($sale);
        }

        return $this->redirectToReferer($this->generateResourcePath($sale, 'show'));
    }

    /**
     * Move down the resource.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moveDownAction(Request $request)
    {
        $context = $this->loadContext($request);
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
        $item = $context->getResource($this->config->getResourceName());
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($this->getParentConfiguration()->getResourceName());

        $this->isGranted('EDIT', $item);

        if (!$item->isLast()) {
            $item->setPosition($item->getPosition() + 1);

            // TODO use ResourceManager
            $this->getOperator()->update($item);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->buildXhrSaleViewResponse($sale);
        }

        return $this->redirectToReferer($this->generateResourcePath($sale, 'show'));
    }

    /**
     * Prioritize the sale item stock.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function prioritizeAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException("Only XHR is supported");
        }

        $context = $this->loadContext($request);
        $resourceName = $this->config->getResourceName();

        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $item */
        $item = $context->getResource($resourceName);

        $prioritizer = $this->get('ekyna_commerce.stock_prioritizer');

        if (!$prioritizer->canPrioritizeSaleItem($item)) {
            throw $this->createNotFoundException("Can't prioritize this item.");
        }

        $saleName = $this->getParentConfiguration()->getResourceName();
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $sale */
        $sale = $context->getResource($saleName);

        $this->isGranted('EDIT', $item);

        $action = $this->generateUrl($this->getConfiguration()->getRoute('prioritize'), [
            $saleName . 'Id'     => $sale->getId(),
            $saleName . 'ItemId' => $item->getId(),
        ]);

        $data = [
            'quantity' => $item->getTotalQuantity(),
        ];

        $form = $this->createForm(SaleItemPrioritizeType::class, $data, [
            'method'       => 'post',
            'action'       => $action,
            'admin_mode'   => true,
            'attr'         => [
                'class' => 'form-horizontal',
            ],
            'max_quantity' => $item->getTotalQuantity(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $changed = $this
                ->get('ekyna_commerce.stock_prioritizer')
                ->prioritizeSaleItem($item, $form->get('quantity')->getData());

            if ($changed) {
                $this->getManager()->flush();
            }

            return $this->buildXhrSaleViewResponse($sale);
        }

        $modal = $this->createModal('new', 'ekyna_commerce.sale.header.item.prioritize');
        $vars = $context->getTemplateVars();
        unset($vars['form_template']);
        $modal
            ->setSize(Modal::SIZE_NORMAL)
            ->setContent($form->createView())
            ->setVars($vars);

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * @inheritdoc
     */
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
            '@EkynaCommerce/Admin/Common/Item/remove.html.twig',
            $context->getTemplateVars([
                // TODO 'form' => $form->createView(),
            ])
        );
    }
}
