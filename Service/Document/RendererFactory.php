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
            'debug' => false,
        ], $config);
    }

    /**
     * Returns a new renderer.
     *
     * @param object      $subject The subjects
     * @param string|null $type    The document type
     *
     * @return RendererInterface
     */
    public function createRenderer(object $subject, string $type = null): RendererInterface
    {
        if ($subject instanceof SupplierOrderInterface) {
            $renderer = new SupplierOrderRenderer($subject);
        } elseif ($subject instanceof ShipmentInterface) {
            $renderer = new ShipmentRenderer($subject, $type);
        } elseif ($subject instanceof InvoiceInterface) {
            $renderer = new InvoiceRenderer($subject);
        } elseif ($subject instanceof DocumentInterface) {
            $renderer = new DocumentRenderer($subject);
        } else {
            throw new InvalidArgumentException('Unsupported subject.');
        }

        $renderer->setTwig($this->twig);
        $renderer->setPdfGenerator($this->pdfGenerator);
        $renderer->setConfig($this->config);

        return $renderer;
    }
}
