<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Document;

use DateTimeInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Resource\Exception\PdfException;
use Ekyna\Component\Resource\Helper\PdfGenerator;
use Ekyna\Component\Resource\Model\TimestampableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Twig\Environment;

use function array_replace;
use function file_put_contents;
use function is_null;
use function reset;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;

/**
 * Class AbstractRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Document
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractRenderer implements RendererInterface
{
    protected readonly Environment  $twig;
    protected readonly PdfGenerator $pdfGenerator;
    protected readonly array        $config;
    protected array                 $subjects;


    /**
     * @param object|array<object> $subjects
     */
    public function __construct(object|array $subjects)
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

    private function addSubject(object $subject): void
    {
        if (!$this->supports($subject)) {
            throw new InvalidArgumentException('Unsupported subject.');
        }

        $this->subjects[] = $subject;
    }

    public function setTwig(Environment $twig): void
    {
        $this->twig = $twig;
    }

    public function setPdfGenerator(PdfGenerator $generator): void
    {
        $this->pdfGenerator = $generator;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function create(string $format = RendererInterface::FORMAT_PDF): string
    {
        $this->validateFormat($format);

        $content = $this->getContent($format);

        $path = sys_get_temp_dir() . '/' . uniqid() . '.' . $format;

        if ($format === RendererInterface::FORMAT_PDF) {
            $content = $this->pdfGenerator->generateFromHtml($content);
        }

        if (!file_put_contents($path, $content)) {
            throw new PdfException("Failed to write content into file '$path'.");
        }

        return $path;
    }

    public function render(string $format = RendererInterface::FORMAT_HTML): string
    {
        $this->validateFormat($format);

        $content = $this->getContent($format);

        if ($format === RendererInterface::FORMAT_HTML) {
            return $content;
        }

        return $this->pdfGenerator->generateFromHtml($content);
    }

    public function respond(Request $request): Response
    {
        $format = $request->attributes->get('_format', RendererInterface::FORMAT_HTML);

        $this->validateFormat($format);

        $download = $request->query->getBoolean('_download');

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

        if ($format === RendererInterface::FORMAT_HTML) {
            return $response;
        }

        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }

    /**
     * Validates the format.
     */
    protected function validateFormat(string $format): void
    {
        if (in_array($format, RendererInterface::FORMATS, true)) {
            return;
        }

        throw new PdfException("Unsupported format '$format'.");
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
        return $this->twig->render('@EkynaCommerce/Document/render.html.twig', array_replace([
            'debug'    => $this->config['debug'],
            'format'   => $format,
            'subjects' => $this->subjects,
            'template' => $this->getTemplate(),
        ], $this->getParameters()));
    }

    public function getLastModified(): ?DateTimeInterface
    {
        if (empty($this->subjects)) {
            throw new LogicException('Call addSubject() first.');
        }

        $subject = null;

        if (1 === count($this->subjects)) {
            $subject = reset($this->subjects);
        } else {
            foreach ($this->subjects as $s) {
                if (!$s instanceof TimestampableInterface) {
                    continue;
                }

                if (is_null($subject) || ($subject->getUpdatedAt() < $s->getUpdatedAt())) {
                    $subject = $s;
                }
            }
        }

        if ($subject instanceof TimestampableInterface) {
            return $subject->getUpdatedAt();
        }

        return null;
    }

    abstract public function getFilename(): string;

    /**
     * Returns whether this renderer supports the given subject.
     */
    abstract protected function supports(object $subject): bool;

    abstract protected function getTemplate(): string;

    /**
     * Returns the template parameters.
     */
    protected function getParameters(): array
    {
        return [];
    }
}
