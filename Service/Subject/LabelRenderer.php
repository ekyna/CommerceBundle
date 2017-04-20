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
    private EventDispatcherInterface $dispatcher;
    private Environment              $templating;
    private PdfGenerator             $pdfGenerator;


    public function __construct(
        EventDispatcherInterface $dispatcher,
        Environment $twig,
        PdfGenerator $pdfGenerator
    ) {
        $this->dispatcher = $dispatcher;
        $this->templating = $twig;
        $this->pdfGenerator = $pdfGenerator;
    }

    /**
     * Renders the subject labels.
     *
     * @throws PdfException
     */
    public function render(array $labels, string $format = SubjectLabel::FORMAT_LARGE): string
    {
        $content = $this->templating->render('@EkynaCommerce/Admin/Subject/label.html.twig', [
            'labels' => $labels,
            'format' => $format,
        ]);

        return $this->pdfGenerator->generateFromHtml($content, $this->getPdfOptionsByFormat($format));
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
        switch ($format) {
            case SubjectLabel::FORMAT_LARGE:
                return [
                    'paper'   => [
                        'width'  => 62,
                        'height' => 100,
                        'unit'   => 'mm',
                    ],
                    'margins' => [
                        'bottom' => 4,
                        'left'   => 4,
                        'right'  => 4,
                        'top'    => 4,
                        'unit'   => 'mm',
                    ],
                ];
            case SubjectLabel::FORMAT_SMALL:
                return [
                    'paper'   => [
                        'width'  => 62,
                        'height' => 29,
                        'unit'   => 'mm',
                    ],
                    'margins' => [
                        'bottom' => 3,
                        'left'   => 3,
                        'right'  => 3,
                        'top'    => 3,
                        'unit'   => 'mm',
                    ],
                ];
        }

        throw new InvalidArgumentException("Unexpected format '$format'.");
    }
}
