<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Admin;

use Ekyna\Bundle\AdminBundle\Controller\Context;
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
     * @inheritdoc
     */
    protected function fetchChildrenResources(array &$data, Context $context)
    {
        return;
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
            'vars_builder' => $this->getSaleViewVarsBuilder(),
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

    /**
     * Returns the view vars builder.
     *
     * @return \Ekyna\Component\Commerce\Common\View\ViewVarsBuilderInterface
     */
    protected function getSaleViewVarsBuilder()
    {
        if ($this->hasParent()) {
            $prefix = $this->config->getParentId();
        } else {
            $prefix = $this->config->getResourceId();
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get(sprintf('%s.view_vars_builder', $prefix));
    }
}
