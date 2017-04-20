<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item;

use Ekyna\Bundle\AdminBundle\Action\UpdateAction as BaseAction;
use Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\XhrTrait;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use function array_replace_recursive;

/**
 * Class UpdateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Sale\Item
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UpdateAction extends BaseAction
{
    use XhrTrait;

    protected function onInit(): ?Response
    {
        $item = $this->context->getResource();

        if (!$item instanceof SaleItemInterface) {
            throw new UnexpectedTypeException($item, SaleItemInterface::class);
        }

        return null;
    }

    protected function getForm(array $options = []): FormInterface
    {
        /** @var SaleItemInterface $item */
        $item = $this->context->getResource();

        $options['currency'] = $item->getSale()->getCurrency()->getCode();

        return parent::getForm($options);
    }

    protected function onPostPersist(): ?Response
    {
        /** @var SaleItemInterface $item */
        $item = $this->context->getResource();

        if ($this->request->isXmlHttpRequest()) {
            return $this->buildXhrSaleViewResponse($item->getSale());
        }

        return $this->redirect($this->generateResourcePath($item->getSale()));
    }

    public static function configureAction(): array
    {
        return array_replace_recursive(parent::configureAction(), [
            'name'    => 'commerce_sale_item_update',
            'button'  => [
                'label'        => 'sale.button.item.edit',
                'trans_domain' => 'EkynaCommerce',
            ],
            'options' => [
                'template'      => '@EkynaCommerce/Admin/Common/Item/update.html.twig',
                'form_template' => '@EkynaCommerce/Admin/Common/Item/_form.html.twig',
            ],
        ]);
    }
}
