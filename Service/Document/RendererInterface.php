<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use DateTimeInterface;
use Ekyna\Component\Resource\Exception\PdfException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface RendererInterface
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface RendererInterface
{
    public const FORMAT_HTML = 'html';
    public const FORMAT_PDF  = 'pdf';

    public const FORMATS = [
        self::FORMAT_HTML,
        self::FORMAT_PDF,
    ];

    /**
     * Create the document file.
     *
     * @return string The file path
     *
     * @throws PdfException
     */
    public function create(string $format = RendererInterface::FORMAT_PDF): string;

    /**
     * Renders the document.
     *
     * @return string The content
     *
     * @throws PdfException
     */
    public function render(string $format = RendererInterface::FORMAT_HTML): string;

    /**
     * Generates a response with the document.
     *
     * @throws PdfException
     */
    public function respond(Request $request): Response;

    /**
     * Returns the document's last modification date.
     */
    public function getLastModified(): ?DateTimeInterface;

    /**
     * Returns the document's filename.
     */
    public function getFilename(): string;
}
