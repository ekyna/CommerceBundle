<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item;

use Ekyna\Bundle\AdminBundle\Action\CreateAction as BaseAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\XhrTrait;
use Ekyna\Component\Commerce\Common\Context\ContextProvider;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use function array_replace_recursive;

/**
 * Class CreateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateAction extends BaseAction
{
    use XhrTrait;

    public function __construct(
        private readonly ContextProvider        $contextProvider,
        private readonly FactoryHelperInterface $factoryHelper
    ) {
    }

    protected function createResource(): ResourceInterface
    {
        /** @var SaleInterface $sale */
        $sale = $this->context->getParentResource();

        $this->contextProvider->setContext($sale);

        return $this->factoryHelper->createItemForSale($sale);
    }

    protected function getForm(array $options = []): FormInterface
    {
        /** @var SaleInterface $sale */
        $sale = $this->context->getParentResource();

        $options['currency'] = $sale->getCurrency()->getCode();

        return parent::getForm($options);
    }

    protected function doPersist(): ResourceEventInterface
    {
        /** @var SaleInterface $sale */
        $sale = $this->context->getParentResource();
        /** @var SaleItemInterface $item */
        $item = $this->context->getResource();

        $sale->addItem($item);

        return $this->getManager($sale)->save($sale);
    }

    protected function onPostPersist(): ?Response
    {
        /** @var SaleInterface $sale */
        $sale = $this->context->getParentResource();

        if ($this->request->isXmlHttpRequest()) {
            return $this->buildXhrSaleViewResponse($sale);
        }

        return $this->redirect($this->generateResourcePath($sale));
    }

    public static function configureAction(): array
    {
        return array_replace_recursive(parent::configureAction(), [
            'name'    => 'commerce_sale_item_create',
            'button'  => [
                'label'        => 'sale.button.item.new',
                'trans_domain' => 'EkynaCommerce',
            ],
            'options' => [
                'template'      => '@EkynaCommerce/Admin/Common/Item/create.html.twig',
                'form_template' => '@EkynaCommerce/Admin/Common/Item/_form.html.twig',
            ],
        ]);
    }
}
