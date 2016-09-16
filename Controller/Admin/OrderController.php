<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Context;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class OrderController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderController extends AbstractSaleController
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
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $order */
        $order = $context->getResource();

        $saleHelper = $this->get('ekyna_commerce.helper.sale');
        $form = $saleHelper->createQuantitiesForm($order, [
            'method' => 'post',
            'action' => $this->generateUrl(
                'ekyna_commerce_order_details_update',
                ['orderId' => $order->getId()]
            ),
        ]);

        $view = $saleHelper->buildView($order);
        $view->vars['form'] = $form->createView();

        $data['order_view'] = $view;

        return null;
    }

    /**
     * Updates the items quantities.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailsUpdateAction(Request $request)
    {
        if (!($request->isXmlHttpRequest() && $request->getMethod() === 'POST')) {
            throw new NotFoundHttpException();
        }

        $context = $this->loadContext($request);
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleInterface $order */
        $order = $context->getResource();

        $saleHelper = $this->get('ekyna_commerce.helper.sale');
        $form = $saleHelper->createQuantitiesForm($order, [
            'method' => 'post',
            'action' => $this->generateUrl(
                'ekyna_commerce_order_details_update',
                ['orderId' => $order->getId()]
            ),
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $event = $this->getOperator()->update($order);

            if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }
        }

        $view = $saleHelper->buildView($order);
        $view->vars['form'] = $form->createView();

        return $this->render('EkynaCommerceBundle:Common:response.xml.twig', [
            'sale_view' => $view,
        ]);
    }
}
