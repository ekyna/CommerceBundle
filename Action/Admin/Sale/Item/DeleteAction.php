<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item;

use Ekyna\Bundle\AdminBundle\Action\DeleteAction as BaseAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\XhrTrait;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\Response;

use function array_replace_recursive;

/**
 * Class DeleteAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DeleteAction extends BaseAction
{
    use XhrTrait;

    protected function onInit(): ?Response
    {
        $item = $this->context->getResource();

        if (!$item instanceof SaleItemInterface) {
            throw new UnexpectedTypeException($item, SaleItemInterface::class);
        }

        if ($item->isImmutable()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        return null;
    }

    protected function onPostPersist(): ?Response
    {
        /** @var SaleItemInterface $item */
        $item = $this->context->getResource();

        if ($this->request->isXmlHttpRequest()) {
            return $this->buildXhrSaleViewResponse($item->getRootSale());
        }

        return $this->redirect($this->generateResourcePath($item->getRootSale()));
    }

    public static function configureAction(): array
    {
        return array_replace_recursive(parent::configureAction(), [
            'name'    => 'commerce_sale_item_delete',
            'button'  => [
                'label'        => 'sale.button.item.remove',
                'trans_domain' => 'EkynaCommerce',
            ],
            'options' => [
                'template' => '@EkynaCommerce/Admin/Common/Item/delete.html.twig',
            ],
        ]);
    }
}
