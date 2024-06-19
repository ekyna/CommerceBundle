<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Mailer;

use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererInterface;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectLabelRenderer;
use Ekyna\Component\Commerce\Common\Model\AttachmentInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentTypes as CDocumentTypes;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Resource\Exception\PdfException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Mime\Email;

use function pathinfo;

use const PATHINFO_BASENAME;

/**
 * Class MailerHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Mailer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AttachmentHelper
{
    public function __construct(
        private readonly FilesystemOperator     $filesystem,
        private readonly RendererFactory        $rendererFactory,
        private readonly SubjectHelperInterface $subjectHelper,
        private readonly SubjectLabelRenderer   $subjectLabelRenderer,
    ) {
    }

    public function attach(Email $email, AttachmentInterface $attachment, string $filename = null): string
    {
        $path = $attachment->getPath();

        if (!$this->filesystem->fileExists($path)) {
            throw new RuntimeException("Attachment file '$path' not found.");
        }

        $content = $this->filesystem->readStream($path);
        $filename = $filename ?? pathinfo($path, PATHINFO_BASENAME);
        $mimeType = $this->filesystem->mimeType($path);

        $email->attach($content, $filename, $mimeType);

        return $filename;
    }

    public function attachInvoice(Email $email, InvoiceInterface $invoice, string $filename = null): string
    {
        $renderer = $this->rendererFactory->createRenderer($invoice);

        try {
            $content = $renderer->render(RendererInterface::FORMAT_PDF);
        } catch (PdfException) {
            throw new RuntimeException('Failed to generate invoice form.');
        }

        $filename = $filename ?? $renderer->getFilename() . '.pdf';

        $email->attach($content, $filename, 'application/pdf');

        return $filename;
    }

    public function attachShipment(Email $email, ShipmentInterface $shipment, string $filename = null): string
    {
        $renderer = $this->rendererFactory->createRenderer($shipment, CDocumentTypes::TYPE_SHIPMENT_BILL);

        try {
            $content = $renderer->render(RendererInterface::FORMAT_PDF);
        } catch (PdfException) {
            throw new RuntimeException('Failed to generate shipment bill.');
        }

        $filename = $filename ?? $renderer->getFilename() . '.pdf';

        $email->attach($content, $filename, 'application/pdf');

        return $filename;
    }

    public function attachSupplierOrder(Email $email, SupplierOrderInterface $order, string $filename = null): string
    {
        $renderer = $this->rendererFactory->createRenderer($order);

        try {
            $content = $renderer->render(RendererInterface::FORMAT_PDF);
        } catch (PdfException) {
            throw new RuntimeException('Failed to generate supplier order form.');
        }

        $filename = $filename ?? $renderer->getFilename() . '.pdf';

        $email->attach($content, $filename, 'application/pdf');

        return $filename;
    }

    public function attachSupplierOrderSubjectLabels(Email $email, SupplierOrderInterface $order): void
    {
        $subjects = [];
        foreach ($order->getItems() as $item) {
            if ($subject = $this->subjectHelper->resolve($item)) {
                $subjects[] = $subject;
            }
        }

        if (empty($subjects)) {
            return;
        }

        $pdf = $this->subjectLabelRenderer->render(SubjectLabelRenderer::FORMAT_LARGE, $subjects, [
            'supplierOrder' => $order,
        ]);

        $email->attach(
            $pdf,
            'labels.pdf',
            'application/pdf'
        );
    }
}
