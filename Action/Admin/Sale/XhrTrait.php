<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale;

use Ekyna\Bundle\CommerceBundle\Service\Common\SaleViewHelper;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\View\SaleView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait XhrTrait
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait XhrTrait
{
    use TemplatingTrait;
    use ManagerTrait;
    use FormTrait;
    use HelperTrait;

    protected readonly SaleViewHelper $saleViewHelper;

    public function setSaleViewHelper(SaleViewHelper $saleViewHelper): void
    {
        $this->saleViewHelper = $saleViewHelper;
    }

    protected function buildQuantitiesForm(SaleInterface $sale): FormInterface
    {
        return $this->saleViewHelper->buildQuantitiesForm($sale);
    }

    protected function buildSaleView(SaleInterface $sale, FormInterface $form = null): SaleView
    {
        $editable = !$sale->isReleased();

        $view = $this->saleViewHelper->buildSaleView($sale, [
            'private'  => true,
            'editable' => $editable,
        ]);

        if (!$editable) {
            return $view;
        }

        if (null === $form) {
            $form = $this->buildQuantitiesForm($sale);
        }

        $view->vars['quantities_form'] = $form->createView();

        return $view;
    }

    protected function buildXhrSaleViewResponse(SaleInterface $sale, FormInterface $form = null): Response
    {
        // We need to refresh the sale to get proper "id/position indexed" collections.
        // TODO move to resource listener : refresh all collections indexed by "id" or "position"
        // TODO get the proper operator through resource registry
        $this->getManager($sale)->refresh($sale);

        $response = $this->render('@EkynaCommerce/Admin/Common/Sale/response.xml.twig', [
            'sale'      => $sale,
            'sale_view' => $this->buildSaleView($sale),
        ]);

        $response->headers->set('Content-type', 'application/xml');

        return $response;
    }
}
