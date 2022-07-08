<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Subject;

use Ekyna\Bundle\CommerceBundle\Event\SubjectLabelEvent;
use Ekyna\Bundle\CommerceBundle\Model\SubjectLabel;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Exception\PdfException;
use Ekyna\Component\Resource\Helper\PdfGenerator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

/**
 * Class LabelRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LabelRenderer
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly Environment              $twig,
        private readonly PdfGenerator             $pdfGenerator
    ) {
    }

    /**
     * Renders the subject labels.
     *
     * @throws PdfException
     */
    public function render(array $labels, string $format = SubjectLabel::FORMAT_LARGE): string
    {
        $content = $this->twig->render('@EkynaCommerce/Admin/Subject/label.html.twig', [
            'labels' => $labels,
            'format' => $format,
        ]);

        $options = $this->getPdfOptionsByFormat($format);

        return $this->pdfGenerator->generateFromHtml($content, $options);
    }

    /**
     * Builds the subject labels.
     *
     * @return array<SubjectLabel>
     */
    public function buildLabels(array $subjects): array
    {
        $event = new SubjectLabelEvent();

        foreach ($subjects as $subject) {
            $label = new SubjectLabel($subject);

            $event->addLabel($label);
        }

        $this->dispatcher->dispatch($event, SubjectLabelEvent::BUILD);

        return $event->getLabels();
    }

    /**
     * Returns the pdf options for the given format.
     */
    private function getPdfOptionsByFormat(string $format): array
    {
        if (SubjectLabel::FORMAT_LARGE === $format) {
            return [
                'unit'         => 'mm',
                'marginTop'    => 4,
                'marginBottom' => 4,
                'marginLeft'   => 4,
                'marginRight'  => 4,
                'paperWidth'   => 62,
                'paperHeight'  => 100,
            ];
        }

        if (SubjectLabel::FORMAT_SMALL === $format) {
            return [
                'unit'         => 'mm',
                'marginTop'    => 3,
                'marginBottom' => 3,
                'marginLeft'   => 3,
                'marginRight'  => 3,
                'paperWidth'   => 62,
                'paperHeight'  => 29,
            ];
        }

        throw new InvalidArgumentException("Unexpected format '$format'.");
    }
}
