<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Context;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SaleController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleController extends AbstractSaleController
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
}
