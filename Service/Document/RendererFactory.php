<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
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
     * @param GeneratorInterface $imageGenerator
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
     * Returns a new renderer.
     *
     * @param mixed  $subjects The subjects
     * @param string $type     The document type
     *
     * @return RendererInterface
     */
    public function createRenderer($subjects, $type = null)
    {
        if (is_array($subjects)) {
            $subject = current($subjects);
        } else {
            $subject = $subjects;
            $subjects = [$subjects];
        }

        if ($subject instanceof SupplierOrderInterface) {
            $renderer = new SupplierOrderRenderer($subjects);
        } elseif ($subject instanceof ShipmentInterface) {
            $renderer = new ShipmentRenderer($subjects, $type);
        } elseif ($subject instanceof InvoiceInterface) {
            $renderer = new InvoiceRenderer($subjects);
        } elseif ($subject instanceof DocumentInterface) {
            $renderer = new DocumentRenderer($subjects);
        } else {
            throw new InvalidArgumentException("Unsupported subject.");
        }

        $renderer->setTemplating($this->templating);
        $renderer->setPdfGenerator($this->pdfGenerator);
        $renderer->setImageGenerator($this->imageGenerator);
        $renderer->setLogoPath($this->logoPath);
        $renderer->setDebug($this->debug);

        return $renderer;
    }
}
