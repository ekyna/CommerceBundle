<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierOrder;

use Ekyna\Bundle\AdminBundle\Action\AbstractCreateFlowAction;
use Ekyna\Bundle\ResourceBundle\Action\RepositoryTrait;
use Ekyna\Component\Resource\Model\ResourceInterface;

use function array_replace_recursive;

/**
 * Class CreateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\SupplierOrder
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateAction extends AbstractCreateFlowAction
{
    use RepositoryTrait;

    protected function createResource(): ResourceInterface
    {
        // TODO supplier from query parameter
        // + use supplier order updater

        return parent::createResource();
    }

    public static function configureAction(): array
    {
        return array_replace_recursive(parent::configureAction(), [
            'name'    => 'commerce_supplier_order_create',
            'options' => [
                'template'      => '@EkynaCommerce/Admin/SupplierOrder/create.html.twig',
                'form_template' => '@EkynaCommerce/Admin/SupplierOrder/_flow.html.twig',
            ],
        ]);
    }
}
