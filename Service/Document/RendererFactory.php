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
     * @var array
     */
    protected $config;


    /**
     * Constructor.
     *
     * @param EngineInterface    $templating
     * @param GeneratorInterface $pdfGenerator
     * @param GeneratorInterface $imageGenerator
     * @param array              $config
     */
    public function __construct(
        EngineInterface $templating,
        GeneratorInterface $pdfGenerator,
        GeneratorInterface $imageGenerator,
        array $config = []
    ) {
        $this->templating = $templating;
        $this->pdfGenerator = $pdfGenerator;
        $this->imageGenerator = $imageGenerator;

        $this->config = array_replace([
            'shipment_remaining_date' => true,
            'logo_path'               => null,
            'debug'                   => false,
        ], $config);
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
        if (is_object($subjects)) {
            $subject = $subjects;
            $subjects = [$subjects];
        } elseif (is_array($subjects)) {
            $subject = reset($subjects);
        } else {
            throw new InvalidArgumentException("Unexpected subjects.");
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
        $renderer->setConfig($this->config);

        return $renderer;
    }
}
