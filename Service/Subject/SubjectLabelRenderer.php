<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Subject;

use Ekyna\Bundle\CommerceBundle\Event\BuildSubjectLabels;
use Ekyna\Bundle\CommerceBundle\Model\SubjectLabel;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Exception\PdfException;
use Ekyna\Component\Resource\Helper\PdfGenerator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

use function array_replace_recursive;

/**
 * Class SubjectLabelRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectLabelRenderer
{
    public const FORMAT_LARGE = 'large';
    public const FORMAT_SMALL = 'small';

    private const FORMAT_DEFAULTS = [
        'title'    => null,
        'template' => null,
        'pdf'      => [
            'unit'         => 'mm',
            'marginTop'    => 4,
            'marginBottom' => 4,
            'marginLeft'   => 4,
            'marginRight'  => 4,
            'paperWidth'   => 62,
            'paperHeight'  => 100,
        ],
    ];

    private array $formats = [
        self::FORMAT_LARGE => [
            'title'    => ['subject.label_format.large', [], 'EkynaCommerce'],
            'template' => '@EkynaCommerce/Admin/Subject/Label/large.html.twig',
            'pdf'      => [
                'unit'         => 'mm',
                'marginTop'    => 4,
                'marginBottom' => 4,
                'marginLeft'   => 4,
                'marginRight'  => 4,
                'paperWidth'   => 62,
                'paperHeight'  => 100,
            ],
        ],
        self::FORMAT_SMALL => [
            'title'    => ['subject.label_format.small', [], 'EkynaCommerce'],
            'template' => '@EkynaCommerce/Admin/Subject/Label/small.html.twig',
            'pdf'      => [
                'unit'         => 'mm',
                'marginTop'    => 3,
                'marginBottom' => 3,
                'marginLeft'   => 3,
                'marginRight'  => 3,
                'paperWidth'   => 62,
                'paperHeight'  => 29,
            ],
        ],
    ];

    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly Environment $twig,
        private readonly PdfGenerator $pdfGenerator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * Registers a label format.
     */
    public function registerFormat(string $name, array $config): void
    {
        $defaults = $this->formats[$name] ?? self::FORMAT_DEFAULTS;

        $config = array_replace_recursive($defaults, $config);

        if (!isset($config['template'])) {
            throw new InvalidArgumentException('You must configure the label format\'s template.');
        }

        $this->formats[$name] = $config;
    }

    public function getFormatChoices(): array
    {
        $choices = [];

        foreach ($this->formats as $name => $format) {
            if (is_array($title = $format['title'])) {
                $title = $this->translator->trans(...$format['title']);
            }
            $choices[$title] = $name;
        }

        return $choices;
    }

    /**
     * Renders the subject labels.
     *
     * @throws PdfException
     */
    public function render(string $format, array $subjects, array $parameters = []): string
    {
        if (!isset($this->formats[$format])) {
            throw new InvalidArgumentException("Unknown '$format' label format.");
        }

        $event = new BuildSubjectLabels($format, $parameters);

        foreach ($subjects as $subject) {
            $event->addLabel(new SubjectLabel($subject));
        }

        $this->dispatcher->dispatch($event);

        $format = $this->formats[$format];

        $content = $this->twig->render($format['template'], [
            'labels' => $event->getLabels(),
        ]);

        return $this->pdfGenerator->generateFromHtml($content, $format['pdf']);
    }
}
