<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Adjustment;

use Ekyna\Bundle\AdminBundle\Action\CreateAction as BaseAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\XhrTrait;
use Ekyna\Component\Commerce\Common\Model\AdjustableInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\Response;

use function array_replace_recursive;

/**
 * Class CreateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Adjustment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateAction extends BaseAction
{
    use XhrTrait;

    protected function onInit(): ?Response
    {
        $adjustment = $this->context->getResource();

        if (!$adjustment instanceof AdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, AdjustmentInterface::class);
        }

        $parent = $this->context->getParentResource();

        if (!$parent instanceof AdjustableInterface) {
            throw new UnexpectedTypeException($parent, AdjustableInterface::class);
        }

        $parent->addAdjustment($adjustment);

        return parent::onInit();
    }

    protected function getFormOptions(): array
    {
        $options = [
            'types' => [
                AdjustmentTypes::TYPE_DISCOUNT,
            ],
        ];

        if ($this->context->getResource() instanceof SaleItemInterface) {
            $options['modes'] = AdjustmentModes::MODE_PERCENT;
        }

        return $options;
    }

    protected function onPostPersist(): ?Response
    {
        $sale = $this->context->getParentResource();

        if ($sale instanceof SaleItemInterface) {
            $sale = $sale->getRootSale();
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
            'name'    => 'commerce_sale_adjustment_create',
            'button'  => [
                'label'        => 'sale.button.adjustment.new',
                'trans_domain' => 'EkynaCommerce',
            ],
            'options' => [
                'template'      => '@EkynaCommerce/Admin/Common/Adjustment/create.html.twig',
                'form_template' => '@EkynaCommerce/Admin/Common/Adjustment/_form.html.twig',
            ],
        ]);
    }
}
