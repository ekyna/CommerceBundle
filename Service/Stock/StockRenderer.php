<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Stock;

use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Group;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Twig\Environment;

use function array_merge;
use function array_replace;
use function array_unique;
use function explode;
use function implode;
use function uniqid;

/**
 * Class StockRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockRenderer
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly Environment         $twig,
        private readonly string              $viewTemplate = '@EkynaCommerce/Admin/Stock/stock_view.html.twig',
        private readonly string              $unitTemplate = '@EkynaCommerce/Admin/Stock/stock_units.html.twig',
        private readonly string              $assignmentTemplate = '@EkynaCommerce/Admin/Stock/stock_assignments.html.twig',
        private readonly string              $subjectsTemplate = '@EkynaCommerce/Admin/Stock/subjects_stock.html.twig'
    ) {
    }

    /**
     * Renders the subject's stock view.
     *
     * @param StockSubjectInterface $subject
     * @param array                 $options
     *
     * @return string
     */
    public function renderSubjectStockView(StockSubjectInterface $subject, array $options = []): string
    {
        $options = array_replace([
            'template' => $this->viewTemplate,
            'prefix'   => 'subject',
        ], $options);

        $id = $options['id'] ?? $options['prefix'] . '_' . uniqid();

        $normalized = $this->normalizer->normalize($subject, 'json', ['groups' => [Group::STOCK_VIEW]]);

        return $this->twig->render($options['template'], [
            'subject' => $normalized,
            'prefix'  => $options['prefix'],
            'id'      => $id,
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

        $normalized = $this->normalizer->normalize($subject, 'json', [
            'groups' => [Group::STOCK_UNIT],
        ]);

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

        $normalized = $this->normalizer->normalize($assignments, 'json', ['groups' => [Group::STOCK_ASSIGNMENT]]);

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
        $template = $options['template'] ?? $this->subjectsTemplate;
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
}
