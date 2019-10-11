<?php

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CoreBundle\Modal\Modal;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AbstractSaleController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleController extends AbstractController
{
    /**
     * Builds the recalculate form.
     *
     * @param SaleInterface $sale
     *
     * @return FormInterface
     */
    abstract protected function buildQuantitiesForm(SaleInterface $sale): FormInterface;

    /**
     * Builds the quote view.
     *
     * @param QuoteInterface $quote
     * @param FormInterface  $form The quantities form
     *
     * @return \Ekyna\Component\Commerce\Common\View\SaleView
     */
    protected function buildSaleView(QuoteInterface $quote, FormInterface $form = null)
    {
        $view = $this->getSaleHelper()->buildView($quote, [
            'taxes_view' => false,
            'private'    => false,
            'editable'   => $quote->isEditable(),
        ]);

        if ($quote->isEditable()) {
            if (null === $form) {
                $form = $this->buildQuantitiesForm($quote);
            }
            $view->vars['quantities_form'] = $form->createView();
        }

        return $view;
    }

    /**
     * Returns the XHR quote view response.
     *
     * @param QuoteInterface $quote
     * @param FormInterface  $form The quantities form
     *
     * @return Response
     */
    protected function buildXhrSaleViewResponse(QuoteInterface $quote, FormInterface $form = null): Response
    {
        // We need to refresh the sale to get proper "id/position indexed" collections.
        // TODO move to resource listener : refresh all collections indexed by "id" or "position"
        // TODO get the proper operator through resource registry
        $this->get('ekyna_commerce.quote.operator')->refresh($quote);

        $response = $this->render('@EkynaCommerce/Account/Sale/response.xml.twig', [
            'sale'      => $quote,
            'sale_view' => $this->buildSaleView($quote, $form),
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

    /**
     * Creates a modal.
     *
     * @param string $title
     * @param mixed  $content
     * @param array  $buttons
     *
     * @return Modal
     */
    protected function createModal($title, $content = null, $buttons = []): Modal
    {
        if (empty($buttons)) {
            $buttons = [
                [
                    'id'       => 'submit',
                    'label'    => 'ekyna_core.button.save',
                    'icon'     => 'glyphicon glyphicon-ok',
                    'cssClass' => 'btn-success',
                    'autospin' => true,
                ],
                [
                    'id'       => 'close',
                    'label'    => 'ekyna_core.button.cancel',
                    'icon'     => 'glyphicon glyphicon-remove',
                    'cssClass' => 'btn-default',
                ],
            ];
        }

        return new Modal($title, $content, $buttons);
    }
}
