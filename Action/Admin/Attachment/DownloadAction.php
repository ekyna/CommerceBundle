<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Attachment;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Service\Filesystem\FilesystemHelper;
use Ekyna\Component\Commerce\Common\Model\AttachmentInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Action\Permission;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DownloadAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Attachment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DownloadAction extends AbstractAction implements AdminActionInterface
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function __invoke(): Response
    {
        $resource = $this->context->getResource();

        if (!$resource instanceof AttachmentInterface) {
            throw new UnexpectedTypeException($resource, AttachmentInterface::class);
        }

        $helper = new FilesystemHelper($this->filesystem);

        try {
            return $helper->createFileResponse($resource->getPath());
        } catch (FilesystemException $exception) {
        }

        return new Response('File does not exist or is not available', Response::HTTP_NOT_FOUND);
    }

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
