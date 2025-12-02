<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\BillOfMaterials;

use Ekyna\Bundle\CommerceBundle\Action\Admin\AbstractStateAction;
use Ekyna\Component\Commerce\Manufacture\Model\BillOfMaterialsInterface;
use Ekyna\Component\Commerce\Manufacture\Model\BOMState;
use Ekyna\Component\Resource\Action\Permission;

use function Symfony\Component\Translation\t;

/**
 * Class ArchiveAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\BillOfMaterials
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ArchiveAction extends AbstractStateAction
{
    // TODO Prevent if there is at least one pending production order

    protected function configureTransition(): array
    {
        return [
            'resource'    => BillOfMaterialsInterface::class,
            'from_states' => [BOMState::VALIDATED],
            'to_state'    => BOMState::ARCHIVED,
            'message'     => t('bill_of_materials.message.archive', [], 'EkynaCommerce'),
            'form_data'   => false,
            'title'       => 'archive',
        ];
    }

    public static function configureAction(): array
    {
        return array_replace_recursive(parent::configureAction(), [
            'name'       => 'bill_of_materials_archive',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_archive',
                'path'     => '/archive',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'button.archive',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'fa fa-archive',
            ],
        ]);
    }
}
