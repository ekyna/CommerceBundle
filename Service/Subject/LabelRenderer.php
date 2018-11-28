<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Subject;

use Ekyna\Bundle\CommerceBundle\Event\SubjectLabelEvent;
use Ekyna\Bundle\CommerceBundle\Model\SubjectLabel;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Knp\Snappy\Pdf;
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
     * @var string
     */
    private $pdfBinaryPath;


    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     * @param EngineInterface          $templating
     * @param string                   $path
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        EngineInterface $templating,
        string $path
    ) {
        $this->dispatcher = $dispatcher;
        $this->templating = $templating;
        $this->pdfBinaryPath = $path;
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

        $generator = new Pdf($this->pdfBinaryPath);

        return $generator->getOutputFromHtml($content, $this->getPdfOptionsByFormat($format));
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
                    'dpi'           => 300,
                    'page-width'    => 62,
                    'page-height'   => 100,
                    'margin-bottom' => 0,
                    'margin-left'   => 0,
                    'margin-right'  => 0,
                    'margin-top'    => 0,
                ];
            case SubjectLabel::FORMAT_SMALL:
                return [
                    'dpi'           => 300,
                    'page-width'    => 62,
                    'page-height'   => 29,
                    'margin-bottom' => 0,
                    'margin-left'   => 0,
                    'margin-right'  => 0,
                    'margin-top'    => 0,
                ];
        }

        throw new InvalidArgumentException("Unexpected format '$format'.");
    }
}
