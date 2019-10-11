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
     * @return FormInterface
     */
    protected function buildQuantitiesForm(SaleInterface $sale)
    {
        $config = $this->config;
        while (null !== $parentId = $config->getParentConfigurationId()) {
            $config = $this->get($parentId);
        }

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
     * @param FormInterface $form The quantities form
     *
     * @return \Ekyna\Component\Commerce\Common\View\SaleView
     */
    protected function buildSaleView(SaleInterface $sale, FormInterface $form = null)
    {
        $editable = !($sale->isReleased() && !$this->getParameter('kernel.debug'));

        $view = $this->getSaleHelper()->buildView($sale, [
            'private'  => true,
            'editable' => $editable,
        ]);

        if ($editable) {
            if (null === $form) {
                $form = $this->buildQuantitiesForm($sale);
            }
            $view->vars['quantities_form'] = $form->createView();
        }

        return $view;
    }

    /**
     * Returns the XHR cart view response.
     *
     * @param SaleInterface $sale
     * @param FormInterface $form The quantities form
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function buildXhrSaleViewResponse(SaleInterface $sale, FormInterface $form = null)
    {
        // We need to refresh the sale to get proper "id/position indexed" collections.
        // TODO move to resource listener : refresh all collections indexed by "id" or "position"
        // TODO get the proper operator through resource registry
        $this->getOperator()->refresh($sale);

        $response = $this->render('@EkynaCommerce/Admin/Common/Sale/response.xml.twig', [
            'sale'      => $sale,
            'sale_view' => $this->buildSaleView($sale, $form),
        ]);

        $response->headers->set('Content-type', 'application/xml');

        return $response;
    }

    /**
     * Returns the sale helper.
     *
     * @return \Ekyna\Bundle\CommerceBundle\Service\SaleHelper
     */
    protected function getSaleHelper()
    {
        return $this->get('ekyna_commerce.sale_helper');
    }
}
