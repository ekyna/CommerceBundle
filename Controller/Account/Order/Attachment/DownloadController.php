<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Order\Attachment;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ControllerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Account\OrderResourceHelper;
use Ekyna\Bundle\ResourceBundle\Service\Filesystem\FilesystemHelper;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class DownloadController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Order\Attachment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DownloadController implements ControllerInterface
{
    public function __construct(
        private readonly OrderResourceHelper $resourceHelper,
        private readonly Filesystem          $filesystem,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $customer = $this->resourceHelper->getCustomer();

        $order = $this->resourceHelper->findOrderByCustomerAndNumber($customer, $request->attributes->get('number'));

        $attachment = $this->resourceHelper->findAttachmentByOrderAndId($order, $request->attributes->getInt('id'));

        $fs = new FilesystemHelper($this->filesystem);
        if (!$fs->fileExists($attachment->getPath(), false)) {
            throw new NotFoundHttpException('File not found');
        }

        return $fs->createFileResponse($attachment->getPath(), $request);
    }
}
