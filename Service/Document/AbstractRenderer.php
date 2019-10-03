<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Resource\Model\TimestampableInterface;
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
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $subjects;


    /**
     * Constructor.
     *
     * @param mixed $subjects
     */
    public function __construct($subjects)
    {
        $this->subjects = [];

        if (is_array($subjects)) {
            foreach ($subjects as $subject) {
                $this->addSubject($subject);
            }
        } else {
            $this->addSubject($subjects);
        }
    }

    /**
     * Adds the subject.
     *
     * @param mixed $subject
     */
    private function addSubject($subject)
    {
        if (!$this->supports($subject)) {
            throw new InvalidArgumentException("Unsupported subject.");
        }

        $this->subjects[] = $subject;
    }

    /**
     * Sets the templating engine.
     *
     * @param EngineInterface $templating
     */
    public function setTemplating(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * Sets the pdf generator.
     *
     * @param GeneratorInterface $generator
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
    public function setImageGenerator(GeneratorInterface $generator)
    {
        $this->imageGenerator = $generator;
    }

    /**
     * Sets the config.
     *
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function create($format = RendererInterface::FORMAT_PDF)
    {
        $this->validateFormat($format);

        $content = $this->getContent($format);

        $path = sys_get_temp_dir() . '/' . uniqid() . '.' . $format;

        if ($format === RendererInterface::FORMAT_PDF) {
            $this->pdfGenerator->generateFromHtml($content, $path);
        } elseif ($format === RendererInterface::FORMAT_JPG) {
            $this->imageGenerator->generateFromHtml($content, $path);
        } elseif (!file_put_contents($path, $content)) {
            throw new \RuntimeException("Failed to write content into file '$path'.");
        }

        return $path;
    }

    /**
     * @inheritdoc
     */
    public function render($format = RendererInterface::FORMAT_HTML)
    {
        $this->validateFormat($format);

        $content = $this->getContent($format);

        if ($format !== RendererInterface::FORMAT_HTML) {
            $options = [
                'margin-top'    => "6mm",
                'margin-right'  => "6mm",
                'margin-bottom' => "6mm",
                'margin-left'   => "6mm",
            ];
            if ($format === RendererInterface::FORMAT_PDF) {
                return $this->pdfGenerator->getOutputFromHtml($content, $options);
            } elseif ($format === RendererInterface::FORMAT_JPG) {
                return $this->imageGenerator->getOutputFromHtml($content, $options);
            }
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

        $filename = sprintf('%s.%s', $this->getFilename(), $format);
        $disposition = $download
            ? ResponseHeaderBag::DISPOSITION_ATTACHMENT
            : ResponseHeaderBag::DISPOSITION_INLINE;
        $header = $response->headers->makeDisposition($disposition, $filename);
        $response->headers->set('Content-Disposition', $header);

        if (!$this->config['debug']) {
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
     * Returns the document's content.
     *
     * @param string $format
     *
     * @return string
     */
    protected function getContent(string $format): string
    {
        return $this->templating->render('@EkynaCommerce/Document/render.html.twig', array_replace([
            'debug'     => $this->config['debug'],
            'format'    => $format,
            'logo_path' => $this->config['logo_path'],
            'subjects'  => $this->subjects,
            'template'  => $this->getTemplate(),
        ], $this->getParameters()));
    }

    /**
     * @inheritDoc
     */
    public function getLastModified()
    {
        if (empty($this->subjects)) {
            throw new LogicException("Please add subject(s) first.");
        }

        /** @var TimestampableInterface $subject */
        $subject = null;

        if (1 === count($this->subjects)) {
            $subject = reset($this->subjects);
        } else {
            /** @var TimestampableInterface $s */
            foreach ($this->subjects as $s) {
                if (is_null($subject) || ($subject->getUpdatedAt() < $s->getUpdatedAt())) {
                    $subject = $s;
                }
            }
        }

        return $subject->getUpdatedAt();
    }

    /**
     * @inheritdoc
     */
    abstract function getFilename();

    /**
     * Returns whether the render supports the given subject.
     *
     * @param mixed $subject
     *
     * @return bool
     */
    abstract protected function supports($subject);

    /**
     * Returns the template.
     *
     * @return string
     */
    abstract protected function getTemplate();

    /**
     * Returns the template parameters.
     *
     * @return array
     */
    protected function getParameters()
    {
        return [];
    }
}
