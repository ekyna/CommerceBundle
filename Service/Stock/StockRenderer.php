<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Stock;

use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Twig\Environment;

/**
 * Class StockRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockRenderer
{
    private NormalizerInterface $normalizer;
    private Environment         $twig;
    private string              $unitTemplate;
    private string              $assignmentTemplate;
    private string              $subjectTemplate;


    public function __construct(
        NormalizerInterface $normalizer,
        Environment $twig,
        string $unitTemplate = '@EkynaCommerce/Admin/Stock/stock_units.html.twig',
        string $assignmentTemplate = '@EkynaCommerce/Admin/Stock/stock_assignments.html.twig',
        string $subjectTemplate = '@EkynaCommerce/Admin/Stock/subjects_stock.html.twig'
    ) {
        $this->normalizer = $normalizer;
        $this->twig = $twig;
        $this->unitTemplate = $unitTemplate;
        $this->assignmentTemplate = $assignmentTemplate;
        $this->subjectTemplate = $subjectTemplate;
    }

    /**
     * Renders the stock assignments list.
     *
     * @param StockAssignmentInterface[] $assignments
     * @param array                      $options
     *
     * @return string
     */
    public function renderStockAssignments(array $assignments, array $options = []): string
    {
        $options = array_replace([
            'template' => $this->assignmentTemplate,
            'prefix'   => 'stockAssignments',
            'class'    => null,
            'actions'  => true,
        ], $options);

        $id = $options['id'] ?? $options['prefix'] . '_' . uniqid();

        $classes = ['table', 'table-striped', 'table-hover', 'table-alt-head'];
        if (isset($options['class'])) {
            $classes = array_unique(array_merge($classes, explode(' ', $options['class'])));
        }

        $normalized = $this->normalizer->normalize($assignments, 'json', ['groups' => ['StockAssignment']]);

        return $this->twig->render($options['template'], [
            'stockAssignments' => $normalized,
            'prefix'           => $options['prefix'],
            'id'               => $id,
            'classes'          => implode(' ', $classes),
            'actions'          => $options['actions'],
        ]);
    }

    /**
     * Renders the stock units list.
     *
     * @param StockUnitInterface[] $subjects
     * @param array                $options
     *
     * @return string
     */
    public function renderSubjectsStock(array $subjects, array $options = []): string
    {
        $template = $options['template'] ?? $this->subjectTemplate;
        $id = $options['id'] ?? 'subject';

        $classes = ['table', 'table-striped', 'table-hover'];
        if (isset($options['class'])) {
            $classes = array_unique(array_merge($classes, explode(' ', $options['class'])));
        }

        return $this->twig->render($template, [
            'subjects' => $subjects,
            'prefix'   => $id,
            'classes'  => implode(' ', $classes),
        ]);
    }

    /**
     * Renders the subject's stock units list.
     *
     * @param StockSubjectInterface $subject
     * @param array                 $options
     *
     * @return string
     */
    public function renderSubjectStockUnits(StockSubjectInterface $subject, array $options = []): string
    {
        $options = array_replace([
            'template' => $this->unitTemplate,
            'prefix'   => 'stockUnits',
            'script'   => false,
            'class'    => null,
            'actions'  => true,
        ], $options);

        $id = $options['id'] ?? $options['prefix'] . '_' . uniqid();

        $classes = ['table', 'table-striped', 'table-hover'];
        if (isset($options['class'])) {
            $classes = array_unique(array_merge($classes, explode(' ', $options['class'])));
        }

        $normalized = $this->normalizer->normalize($subject, 'json', ['groups' => ['StockView']]);

        return $this->twig->render($options['template'], [
            'stockUnits' => $normalized['stock_units'],
            'prefix'     => $options['prefix'],
            'id'         => $id,
            'classes'    => implode(' ', $classes),
            'manual'     => $subject->getStockMode() === StockSubjectModes::MODE_MANUAL,
            'script'     => $options['script'],
            'actions'    => $options['actions'],
        ]);
    }
}
