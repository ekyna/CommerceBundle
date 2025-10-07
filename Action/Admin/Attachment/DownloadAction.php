<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Attachment;

use Ekyna\Bundle\AdminBundle\Action\AbstractDownloadAction;
use Ekyna\Component\Resource\Action\Permission;

/**
 * Class DownloadAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Attachment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DownloadAction extends AbstractDownloadAction
{
    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_attachment_download',
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_download',
                'path'     => '/download',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'button.download',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'download',
            ],
        ];
    }
}
