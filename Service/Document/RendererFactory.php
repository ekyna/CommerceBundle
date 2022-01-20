<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Document\Model\DocumentInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Resource\Helper\PdfGenerator;
use Twig\Environment;

/**
 * Class RendererFactory
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RendererFactory
{
    protected Environment  $twig;
    protected PdfGenerator $pdfGenerator;
    protected array        $config;

    public function __construct(
        Environment  $twig,
        PdfGenerator $pdfGenerator,
        array        $config = []
    ) {
        $this->twig = $twig;
        $this->pdfGenerator = $pdfGenerator;

        $this->config = array_replace([
            'shipment_remaining_date' => true,
            'debug'                   => false,
        ], $config);
    }

    /**
     * Returns a new renderer.
     *
     * @param mixed       $subjects The subjects
     * @param string|null $type     The document type
     *
     * @return RendererInterface
     */
    public function createRenderer($subjects, string $type = null): RendererInterface
    {
        if (is_object($subjects)) {
            $subject = $subjects;
            $subjects = [$subjects];
        } elseif (is_array($subjects)) {
            $subject = reset($subjects);
        } else {
            throw new InvalidArgumentException('Unexpected subjects.');
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
            throw new InvalidArgumentException('Unsupported subject.');
        }

        $renderer->setTwig($this->twig);
        $renderer->setPdfGenerator($this->pdfGenerator);
        $renderer->setConfig($this->config);

        return $renderer;
    }
}
