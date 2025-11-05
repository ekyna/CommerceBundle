<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\BillOfMaterials;

use Ekyna\Bundle\CommerceBundle\Action\Admin\AbstractStateAction;
use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Manufacture\Model\BOMState;
use Ekyna\Component\Resource\Action\Permission;

use function Symfony\Component\Translation\t;

/**
 * Class ValidateAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\BillOfMaterials
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ValidateAction extends AbstractStateAction
{
    protected function configureTransition(): array
    {
        return [
            'resource'    => BillOfMaterialsInterface::class,
            'from_states' => [BOMState::DRAFT],
            'to_state'    => BOMState::VALIDATED,
            'message'     => t('bill_of_materials.message.validate', [], 'EkynaCommerce'),
            'form_data'   => false,
            'title'       => 'validate',
        ];
    }

    public static function configureAction(): array
    {
        return array_replace_recursive(parent::configureAction(), [
            'name'       => 'bill_of_materials_validate',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_validate',
                'path'     => '/validate',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label' => 'button.validate',
                'theme' => 'success',
                'icon'  => 'ok',
            ],
        ]);
    }
}
