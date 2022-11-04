<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Attachment;

use Ekyna\Bundle\CommerceBundle\Controller\Account\ControllerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Account\QuoteResourceHelper;
use Ekyna\Bundle\ResourceBundle\Service\Filesystem\FilesystemHelper;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class DownloadController
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account\Quote\Attachment
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DownloadController implements ControllerInterface
{
    public function __construct(
        private readonly QuoteResourceHelper $resourceHelper,
        private readonly Filesystem          $filesystem,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $customer = $this->resourceHelper->getCustomer();

        $quote = $this->resourceHelper->findQuoteByCustomerAndNumber($customer, $request->attributes->get('number'));

        $attachment = $this->resourceHelper->findAttachmentByQuoteAndId($quote, $request->attributes->getInt('id'));

        $fs = new FilesystemHelper($this->filesystem);
        if (!$fs->fileExists($attachment->getPath(), false)) {
            throw new NotFoundHttpException('File not found');
        }

        return $fs->createFileResponse($attachment->getPath(), $request);
    }
}
