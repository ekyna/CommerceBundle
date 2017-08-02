<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Knp\Snappy\GeneratorInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class RendererFactory
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RendererFactory
{
    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var GeneratorInterface
     */
    protected $pdfGenerator;

    /**
     * @var GeneratorInterface
     */
    protected $imageGenerator;

    /**
     * @var string
     */
    protected $logoPath;

    /**
     * @var bool
     */
    protected $debug;


    /**
     * Constructor.
     *
     * @param EngineInterface    $templating
     * @param GeneratorInterface $pdfGenerator
     * @param string             $logoPath
     * @param bool               $debug
     */
    public function __construct(
        EngineInterface $templating,
        GeneratorInterface $pdfGenerator,
        GeneratorInterface $imageGenerator,
        $logoPath,
        $debug
    ) {
        $this->templating = $templating;
        $this->pdfGenerator = $pdfGenerator;
        $this->imageGenerator = $imageGenerator;
        $this->logoPath = $logoPath;
        $this->debug = $debug;
    }

    /**
     * Returns a new invoice renderer.
     *
     * @param InvoiceInterface $invoice
     *
     * @return RendererInterface
     */
    public function createInvoiceRenderer(InvoiceInterface $invoice)
    {
        $renderer = new InvoiceRenderer($invoice);

        $this->buildRenderer($renderer);

        return $renderer;
    }

    /**
     * Returns a new supplier order renderer.
     *
     * @param SupplierOrderInterface $supplierOrder
     *
     * @return RendererInterface
     */
    public function createSupplierOrderRenderer(SupplierOrderInterface $supplierOrder)
    {
        $renderer = new SupplierOrderRenderer($supplierOrder);

        $this->buildRenderer($renderer);

        return $renderer;
    }

    /**
     * Builds the renderer.
     *
     * @param RendererInterface $renderer
     */
    private function buildRenderer(RendererInterface $renderer)
    {
        $renderer->setTemplating($this->templating);
        $renderer->setPdfGenerator($this->pdfGenerator);
        $renderer->setImageGenerator($this->imageGenerator);
        $renderer->setLogoPath($this->logoPath);
        $renderer->setDebug($this->debug);
    }
}
