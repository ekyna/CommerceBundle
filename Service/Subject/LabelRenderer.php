<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Subject;

use Ekyna\Bundle\CommerceBundle\Event\SubjectLabelEvent;
use Ekyna\Bundle\CommerceBundle\Model\SubjectLabel;
use Ekyna\Bundle\CommerceBundle\Service\Document\PdfGenerator;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class LabelRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Subject
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LabelRenderer
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var PdfGenerator
     */
    private $pdfGenerator;


    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     * @param EngineInterface          $templating
     * @param PdfGenerator             $pdfGenerator
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        EngineInterface $templating,
        PdfGenerator $pdfGenerator
    ) {
        $this->dispatcher = $dispatcher;
        $this->templating = $templating;
        $this->pdfGenerator = $pdfGenerator;
    }

    /**
     * Renders the subject labels.
     *
     * @param array  $labels
     * @param string $format
     *
     * @return string
     */
    public function render(array $labels, $format = SubjectLabel::FORMAT_LARGE)
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
     * @param array $subjects
     *
     * @return SubjectLabel[]
     */
    public function buildLabels(array $subjects)
    {
        $event = new SubjectLabelEvent();

        foreach ($subjects as $subject) {
            $label = new SubjectLabel($subject);

            $event->addLabel($label);
        }

        $this->dispatcher->dispatch(SubjectLabelEvent::BUILD, $event);

        return $event->getLabels();
    }

    /**
     * Returns the pdf options for the given format.
     *
     * @param string $format
     *
     * @return array
     */
    private function getPdfOptionsByFormat($format)
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
                        'bottom' => 2,
                        'left'   => 2,
                        'right'  => 2,
                        'top'    => 2,
                        'unit'   => 'mm',
                    ],
                ];
        }

        throw new InvalidArgumentException("Unexpected format '$format'.");
    }
}
