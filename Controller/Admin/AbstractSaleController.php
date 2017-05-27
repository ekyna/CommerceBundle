<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\ResourceController;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Class AbstractSaleController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Admin
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleController extends ResourceController
{
    /**
     * Builds the recalculate form.
     *
     * @param SaleInterface $sale
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function buildRecalculateForm(SaleInterface $sale)
    {
        // TODO 'ekyna_commerce_order_admin_recalculate'
        $config = $this->hasParent() ? $this->getParentConfiguration() : $this->config;

        $parameter = $config->getResourceName() . 'Id';
        $route = $config->getRoute('recalculate');

        return $this->getSaleHelper()->createQuantitiesForm($sale, [
            'method' => 'post',
            'action' => $this->generateUrl($route, [$parameter => $sale->getId()]),
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
        // We need to refresh the sale to get proper "id indexed" collections.
        // TODO move to resource listener : refresh all collections indexed by "id"
        // TODO get the proper operator through resource registry
        $this->getOperator()->refresh($sale);

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
