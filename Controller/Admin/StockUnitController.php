<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints;

/**
 * Class StockUnitController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitController extends ResourceController
{
    /**
     * Adjustment new action.
     *
     * @param Request $request
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function adjustmentNewAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var StockUnitInterface $stockUnit */
        $stockUnit = $context->getResource($resourceName);

        $this->isGranted('EDIT', $stockUnit);

        /** @var StockAdjustmentInterface $stockAdjustment */
        $stockAdjustment = $this->get('ekyna_commerce.stock_adjustment.repository')->createNew();
        $stockAdjustment->setStockUnit($stockUnit);

        $form = $this->createStockAdjustmentForm($stockAdjustment, [
            'action' => $this->generateUrl($this->config->getRoute('adjustment_new'), array_merge(
                $context->getIdentifiers(true),
                $context->getRequest()->query->all()
            ))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            try {
                $event = $this
                    ->get('ekyna_commerce.stock_adjustment.operator')
                    ->create($stockAdjustment);

                if (!$event->hasErrors()) {
                    return $this->createStockViewResponse($stockUnit->getSubject());
                }

                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            } catch (StockLogicException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $modal = $this->createModal('new', 'ekyna_commerce.stock_adjustment.header.new');
        $modal
            ->setContent($form->createView())
            ->setVars([
                'form_template' => 'EkynaCommerceBundle:Admin/Stock:stock_adjustment_form.html.twig'
            ]);

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * Adjustment edit action.
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     */
    public function adjustmentEditAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var StockUnitInterface $stockUnit */
        $stockUnit = $context->getResource($resourceName);

        $this->isGranted('EDIT', $stockUnit);

        /** @var StockAdjustmentInterface $stockAdjustment */
        $stockAdjustment = $this->get('ekyna_commerce.stock_adjustment.repository')->find(
            $request->attributes->get('stockAdjustmentId')
        );

        $form = $this->createStockAdjustmentForm($stockAdjustment, [
            'action' => $this->generateUrl($this->config->getRoute('adjustment_edit'), array_merge(
                $context->getIdentifiers(true),
                ['stockAdjustmentId' => $stockAdjustment->getId()],
                $context->getRequest()->query->all()
            ))
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            try {
                $event = $this
                    ->get('ekyna_commerce.stock_adjustment.operator')
                    ->update($stockAdjustment);

                if (!$event->hasErrors()) {
                    return $this->createStockViewResponse($stockUnit->getSubject());
                }

                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            } catch (StockLogicException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $modal = $this->createModal('edit', 'ekyna_commerce.stock_adjustment.header.edit');
        $modal
            ->setContent($form->createView())
            ->setVars([
                'form_template' => 'EkynaCommerceBundle:Admin/Stock:stock_adjustment_form.html.twig'
            ]);

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * Adjustment remove action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function adjustmentRemoveAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $context = $this->loadContext($request);

        $resourceName = $this->config->getResourceName();
        /** @var StockUnitInterface $stockUnit */
        $stockUnit = $context->getResource($resourceName);

        $this->isGranted('EDIT', $stockUnit);

        /** @var StockAdjustmentInterface $stockAdjustment */
        $stockAdjustment = $this->get('ekyna_commerce.stock_adjustment.repository')->find(
            $request->attributes->get('stockAdjustmentId')
        );

        $form = $this
            ->createFormBuilder(null, [
                'action' => $this->generateUrl($this->config->getRoute('adjustment_remove'), array_merge(
                    $context->getIdentifiers(true),
                    ['stockAdjustmentId' => $stockAdjustment->getId()],
                    $context->getRequest()->query->all()
                )),
                'attr'              => ['class' => 'form-horizontal'],
                'method'            => 'POST',
                'admin_mode'        => true,
                '_redirect_enabled' => true,
            ])
            ->add('confirm', Type\CheckboxType::class, [
                'label'       => 'ekyna_commerce.stock_adjustment.confirm.remove',
                'attr'        => ['align_with_widget' => true],
                'required'    => true,
                'constraints' => [
                    new Constraints\IsTrue(),
                ],
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // TODO use ResourceManager
            try {
                $event = $this
                    ->get('ekyna_commerce.stock_adjustment.operator')
                    ->delete($stockAdjustment);

                if (!$event->hasErrors()) {
                    return $this->createStockViewResponse($stockUnit->getSubject());
                }

                // TODO all event messages should be bound to XHR response
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            } catch (StockLogicException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $modal = $this->createModal('remove', 'ekyna_commerce.stock_adjustment.header.remove');
        $modal
            ->setSize(Modal::SIZE_NORMAL)
            ->setContent($form->createView());

        return $this->get('ekyna_core.modal')->render($modal);
    }

    /**
     * Creates the stock adjustment form.
     *
     * @param StockAdjustmentInterface $stockAdjustment
     * @param array                    $options
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createStockAdjustmentForm(StockAdjustmentInterface $stockAdjustment, array $options = [])
    {
        $config = $this->get('ekyna_commerce.stock_adjustment.configuration');

        $form = $this->createForm($config->getFormType(), $stockAdjustment, array_merge([
            'method'            => 'POST',
            'attr'              => ['class' => 'form-horizontal form-with-tabs'],
            'admin_mode'        => true,
            '_redirect_enabled' => true,
        ], $options));

        return $form;
    }

    /**
     * Creates the subject stock view response.
     *
     * @param StockSubjectInterface $subject
     *
     * @return Response
     */
    private function createStockViewResponse(StockSubjectInterface $subject)
    {
        $serialized = $this->get('serializer')->serialize($subject, 'json', ['groups' => ['StockView']]);

        $response = new Response($serialized, Response::HTTP_OK, [
            'Content-Type' => 'application/json',
        ]);

        return $response->setPrivate();
    }
}