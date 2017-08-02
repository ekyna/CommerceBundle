<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Knp\Snappy\GeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class AbstractRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractRenderer implements RendererInterface
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
     * @inheritdoc
     */
    public function setTemplating(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @inheritdoc
     */
    public function setPdfGenerator(GeneratorInterface $generator)
    {
        $this->pdfGenerator = $generator;
    }

    /**
     * Sets the image generator.
     *
     * @param GeneratorInterface $generator
     */
    public function setImageGenerator($generator)
    {
        $this->imageGenerator = $generator;
    }

    /**
     * @inheritdoc
     */
    public function setLogoPath($logoPath)
    {
        $this->logoPath = $logoPath;
    }

    /**
     * @inheritdoc
     */
    public function setDebug($debug)
    {
        $this->debug = (bool)$debug;
    }

    /**
     * @inheritdoc
     */
    public function render($format = RendererInterface::FORMAT_HTML)
    {
        $this->validateFormat($format);

        $content = $this->getContent();

        if ($format === RendererInterface::FORMAT_PDF) {
            return $this->pdfGenerator->getOutputFromHtml($content);
        } elseif ($format === RendererInterface::FORMAT_JPG) {
            return $this->imageGenerator->getOutputFromHtml($content);
        }

        return $content;
    }

    /**
     * @inheritdoc
     */
    public function respond(Request $request)
    {
        $format = $request->attributes->get('_format', RendererInterface::FORMAT_HTML);

        $this->validateFormat($format);

        $download = !!$request->query->get('_download', false);

        $response = new Response();
        if (!$this->debug) {
            $response->setLastModified($this->getLastModified());
            if ($response->isNotModified($request)) {
                return $response;
            }
        }

        $response->setContent($this->render($format));

        if ($format === RendererInterface::FORMAT_PDF) {
            $response->headers->add(['Content-Type' => 'application/pdf']);
        } elseif ($format === RendererInterface::FORMAT_JPG) {
            $response->headers->add(['Content-Type' => 'image/jpeg']);
        }

        if ($download) {
            $filename = sprintf('%s.%s', $this->getFilename(), $format);
            $contentDisposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename
            );
            $response->headers->set('Content-Disposition', $contentDisposition);
        }

        return $response;
    }

    /**
     * Validates the format.
     *
     * @param string $format
     */
    protected function validateFormat($format)
    {
        if (!in_array($format, [
            RendererInterface::FORMAT_HTML,
            RendererInterface::FORMAT_PDF,
            RendererInterface::FORMAT_JPG,
        ])) {
            throw new InvalidArgumentException("Unsupported format '$format'.");
        }
    }

    /**
     * Renders the view.
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return string
     */
    protected function renderView($name, array $parameters = [])
    {
        return $this->templating->render($name, $parameters);
    }

    /**
     * Returns the document's content.
     *
     * @return string
     */
    abstract protected function getContent();

    /**
     * Returns the document's last modification date.
     *
     * @return \DateTime
     */
    abstract protected function getLastModified();

    /**
     * Returns the document's filename.
     *
     * @return string
     */
    abstract protected function getFilename();
}
