<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\BillOfMaterials;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\CommerceBundle\Action\Admin\ProductionOrder\CreateAction;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CreatePOAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\BillOfMaterials
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CreatePOAction extends AbstractAction implements AdminActionInterface
{
    use HelperTrait;

    public function __invoke(): Response
    {
        $resource = $this->context->getResource();

        if (!$resource instanceof BillOfMaterialsInterface) {
            throw new UnexpectedTypeException($resource, BillOfMaterialsInterface::class);
        }

        return $this->redirect($this->generateResourcePath(ProductionOrderInterface::class, CreateAction::class, [
            'bom' => $resource->getId(),
        ]));
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'bill_of_materials_create_po',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_create_po',
                'path'     => '/create-po',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'production_order.button.new',
                'trans_domain' => 'EkynaCommerce',
                'theme'        => 'success',
                'icon'         => 'wrench',
            ],
        ];
    }
}
