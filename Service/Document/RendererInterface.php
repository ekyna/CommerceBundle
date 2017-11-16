<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface RendererInterface
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface RendererInterface
{
    const FORMAT_HTML = 'html';
    const FORMAT_PDF  = 'pdf';
    const FORMAT_JPG  = 'jpg';


    /**
     * Create the document file.
     *
     * @param string $format
     *
     * @return string The file path
     */
    public function create($format = RendererInterface::FORMAT_PDF);

    /**
     * Renders the document.
     *
     * @param string $format
     *
     * @return string The content
     */
    public function render($format = RendererInterface::FORMAT_HTML);

    /**
     * Generates a response with the document.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respond(Request $request);

    /**
     * Returns the document's last modification date.
     *
     * @return \DateTime
     */
    public function getLastModified();

    /**
     * Returns the document's filename.
     *
     * @return string
     */
    public function getFilename();
}
