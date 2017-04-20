<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Action\Admin\Attachment;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Commerce\Common\Model\AttachmentInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ArchiveAction
 * @package Ekyna\Bundle\CommerceBundle\Action\Admin\Attachment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ArchiveAction extends AbstractAction implements AdminActionInterface
{
    use ManagerTrait;
    use FlashTrait;
    use HelperTrait;

    public function __invoke(): Response
    {
        $resource = $this->context->getResource();

        if (!$resource instanceof AttachmentInterface) {
            throw new UnexpectedTypeException($resource, AttachmentInterface::class);
        }

        $resource
            ->setType(null)
            ->setInternal(true)
            ->setTitle('[archived] ' . $resource->getTitle());

        $event = $this
            ->getManager()
            ->save($resource);

        $this->addFlashFromEvent($event);

        if ($parent = $this->context->getParentResource()) {
            $path = $this->generateResourcePath($parent);
        } else {
            $path = $this->generateResourcePath($resource);
        }

        return $this->redirectToReferer($path);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'commerce_attachment_archive',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_archive',
                'path'     => '/archive',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'button.archive',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'archive',
            ],
        ];
    }
}
