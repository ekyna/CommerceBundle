<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Adjustment;

use Ekyna\Bundle\AdminBundle\Action\UpdateAction as BaseAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\XhrTrait;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\Response;

use function array_replace_recursive;

/**
 * Class UpdateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Adjustment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UpdateAction extends BaseAction
{
    use XhrTrait;

    protected function onInit(): ?Response
    {
        $adjustment = $this->context->getResource();

        if (!$adjustment instanceof AdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, AdjustmentInterface::class);
        }

        if ($adjustment->isImmutable()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        return null;
    }

    protected function onPostPersist(): ?Response
    {
        $sale = $this->context->getParentResource();

        if ($sale instanceof SaleItemInterface) {
            $sale = $sale->getSale();
        }

        if (!$sale instanceof SaleInterface) {
            throw new UnexpectedTypeException($sale, SaleInterface::class);
        }

        if ($this->request->isXmlHttpRequest()) {
            return $this->buildXhrSaleViewResponse($sale);
        }

        return $this->redirect($this->generateResourcePath($sale));
    }

    public static function configureAction(): array
    {
        return array_replace_recursive(parent::configureAction(), [
            'name'    => 'commerce_sale_adjustment_update',
            'button'  => [
                'label'        => 'sale.button.adjustment.edit',
                'trans_domain' => 'EkynaCommerce',
            ],
            'options' => [
                'template'      => '@EkynaCommerce/Admin/Common/Adjustment/update.html.twig',
                'form_template' => '@EkynaCommerce/Admin/Common/Adjustment/_form.html.twig',
            ],
        ]);
    }
}
