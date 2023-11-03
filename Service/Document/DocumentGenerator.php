<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model\SaleAttachmentInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Document\Builder\DocumentBuilderInterface;
use Ekyna\Component\Commerce\Document\Calculator\DocumentCalculatorInterface;
use Ekyna\Component\Commerce\Document\Model\Document;
use Ekyna\Component\Commerce\Document\Util\DocumentUtil;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Exception\PdfException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class DocumentGenerator
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentGenerator
{
    public function __construct(
        protected readonly DocumentBuilderInterface    $documentBuilder,
        protected readonly DocumentCalculatorInterface $documentCalculator,
        protected readonly RendererFactory             $rendererFactory,
        protected readonly FactoryHelperInterface      $factoryHelper,
        protected readonly TranslatorInterface         $translator
    ) {
    }

    /**
     * Generates a document for the given sale and type.
     *
     * @param SaleInterface $sale
     * @param string        $type
     *
     * @return SaleAttachmentInterface
     * @throws PdfException
     */
    public function generate(SaleInterface $sale, string $type): SaleAttachmentInterface
    {
        $available = DocumentUtil::getSaleEditableDocumentTypes($sale);
        if (!in_array($type, $available, true)) {
            throw new InvalidArgumentException('Unexpected document type.');
        }

        // Generate the document file
        $document = new Document();
        $document
            ->setSale($sale)
            ->setType($type);

        $this->documentBuilder->build($document);
        $this->documentCalculator->calculate($document);

        $renderer = $this->rendererFactory->createRenderer($document);

        $path = $renderer->create(RendererInterface::FORMAT_PDF);

        // Fake uploaded file
        $file = new UploadedFile($path, $renderer->getFilename(), null, null, true);

        // Attachment
        $attachment = $this->factoryHelper->createAttachmentForSale($sale);

        $attachment
            ->setType($type)
            ->setTitle($this->translator->trans('document.type.' . $type, [], 'EkynaCommerce'))
            ->setFile($file);

        $sale->addAttachment($attachment);

        return $attachment;
    }
}
