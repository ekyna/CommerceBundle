<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Knp\Snappy\GeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;

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
     * Sets the templating.
     *
     * @param EngineInterface $templating
     */
    public function setTemplating(EngineInterface $templating);

    /**
     * Sets the pdf generator.
     *
     * @param GeneratorInterface $generator
     */
    public function setPdfGenerator(GeneratorInterface $generator);

    /**
     * Sets the image generator.
     *
     * @param GeneratorInterface $generator
     */
    public function setImageGenerator($generator);

    /**
     * Sets the logoPath.
     *
     * @param string $logoPath
     */
    public function setLogoPath($logoPath);

    /**
     * Sets whether to debug.
     *
     * @param bool $debug
     */
    public function setDebug($debug);

    /**
     * Renders the document.
     *
     * @param string $format
     *
     * @return string
     */
    public function render($format = RendererInterface::FORMAT_HTML);

    /**
     * Generates a response with the document.
     *
     * @param Request $request
     */
    public function respond(Request $request);
}
