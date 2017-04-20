<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleQuantitiesType;
use Ekyna\Bundle\ResourceBundle\Action\FormTrait;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\TemplatingTrait;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\View\SaleView;
use Ekyna\Component\Commerce\Common\View\ViewBuilder;
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

    protected ViewBuilder $viewBuilder;

    public function setViewBuilder(ViewBuilder $viewBuilder): void
    {
        $this->viewBuilder = $viewBuilder;
    }

    protected function buildQuantitiesForm(SaleInterface $sale): FormInterface
    {
        return $this->formFactory->create(SaleQuantitiesType::class, $sale, [
            'method' => 'post',
            'action' => $this->generateResourcePath($sale, RecalculateAction::class),
        ]);
    }

    protected function buildSaleView(SaleInterface $sale, FormInterface $form = null): SaleView
    {
        $editable = !$sale->isReleased();

        $view = $this->viewBuilder->buildSaleView($sale, [
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

    protected function buildXhrSaleViewResponse(SaleInterface $sale, FormInterface $form = null): Response
    {
        // We need to refresh the sale to get proper "id/position indexed" collections.
        // TODO move to resource listener : refresh all collections indexed by "id" or "position"
        // TODO get the proper operator through resource registry
        $this->getManager($sale)->refresh($sale);

        $response = $this->render('@EkynaCommerce/Admin/Common/Sale/response.xml.twig', [
            'sale'      => $sale,
            'sale_view' => $this->buildSaleView($sale, $form),
        ]);

        $response->headers->set('Content-type', 'application/xml');

        return $response;
    }
}
