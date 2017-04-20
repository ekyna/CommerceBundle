<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Document\Builder\DocumentBuilderInterface;
use Ekyna\Component\Commerce\Document\Calculator\DocumentCalculatorInterface;
use Ekyna\Component\Commerce\Document\Model\Document;
use Ekyna\Component\Commerce\Document\Util\DocumentUtil;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class DocumentGenerator
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DocumentGenerator
{
    /**
     * @var DocumentBuilderInterface
     */
    protected $documentBuilder;

    /**
     * @var DocumentCalculatorInterface
     */
    protected $documentCalculator;

    /**
     * @var RendererFactory
     */
    protected $rendererFactory;

    /**
     * @var SaleFactoryInterface
     */
    protected $saleFactory;

    /**
     * @var TranslatorInterface
     */
    protected $translator;


    /**
     * Constructor.
     *
     * @param DocumentBuilderInterface    $documentBuilder
     * @param DocumentCalculatorInterface $documentCalculator
     * @param RendererFactory             $rendererFactory
     * @param SaleFactoryInterface        $saleFactory
     * @param TranslatorInterface         $translator
     */
    public function __construct(
        DocumentBuilderInterface $documentBuilder,
        DocumentCalculatorInterface $documentCalculator,
        RendererFactory $rendererFactory,
        SaleFactoryInterface $saleFactory,
        TranslatorInterface $translator
    ) {
        $this->documentBuilder = $documentBuilder;
        $this->documentCalculator = $documentCalculator;
        $this->rendererFactory = $rendererFactory;
        $this->saleFactory = $saleFactory;
        $this->translator = $translator;
    }

    /**
     * Generates a document for the given sale and type.
     *
     * @param SaleInterface $sale
     * @param string        $type
     *
     * @return \Ekyna\Component\Commerce\Common\Model\SaleAttachmentInterface
     *
     * @throws \Ekyna\Component\Resource\Exception\PdfException
     */
    public function generate(SaleInterface $sale, $type)
    {
        $available = DocumentUtil::getSaleEditableDocumentTypes($sale);
        if (!in_array($type, $available, true)) {
            throw new InvalidArgumentException("Unexpected document type.");
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
        $attachment = $this->saleFactory->createAttachmentForSale($sale);

        $attachment
            ->setType($type)
            ->setTitle($this->translator->trans('document.type.' . $type, [], 'EkynaCommerce'))
            ->setFile($file);

        $sale->addAttachment($attachment);

        return $attachment;
    }
}
