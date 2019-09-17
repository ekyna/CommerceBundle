<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Stock;

use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class StockRenderer
 * @package Ekyna\Bundle\CommerceBundle\Service\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockRenderer
{
    /**
     * @var StockUnitResolverInterface
     */
    private $resolver;

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var string
     */
    private $unitTemplate;

    /**
     * @var string
     */
    private $assignmentTemplate;

    /**
     * @var string
     */
    private $subjectTemplate;


    /**
     * Constructor.
     *
     * @param StockUnitResolverInterface $resolver
     * @param NormalizerInterface        $normalizer
     * @param EngineInterface            $templating
     * @param string                     $unitTemplate
     * @param string                     $assignmentTemplate
     * @param string                     $subjectTemplate
     */
    public function __construct(
        StockUnitResolverInterface $resolver,
        NormalizerInterface $normalizer,
        EngineInterface $templating,
        $unitTemplate = '@EkynaCommerce/Admin/Stock/stock_units.html.twig',
        $assignmentTemplate = '@EkynaCommerce/Admin/Stock/stock_assignments.html.twig',
        $subjectTemplate = '@EkynaCommerce/Admin/Stock/subjects_stock.html.twig'
    ) {
        $this->resolver = $resolver;
        $this->normalizer = $normalizer;
        $this->templating = $templating;
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
    public function renderStockAssignments(array $assignments, array $options = [])
    {
        $template = isset($options['template']) ? $options['template'] : $this->assignmentTemplate;
        $prefix = isset($options['prefix']) ? $options['prefix'] : 'stockAssignments';
        $id = isset($options['id']) ? $options['id'] : $prefix . '_' . uniqid();

        $classes = ['table', 'table-striped', 'table-hover', 'table-alt-head'];
        if (isset($options['class'])) {
            $classes = array_unique(array_merge($classes, explode(' ', $options['class'])));
        }

        $normalized = $this->normalizer->normalize($assignments, 'json', ['groups' => ['StockAssignment']]);

        return $this->templating->render($template, [
            'stockAssignments' => $normalized,
            'prefix'           => $prefix,
            'id'               => $id,
            'classes'          => implode(' ', $classes),
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
    public function renderSubjectsStock(array $subjects, array $options = [])
    {
        $template = isset($options['template']) ? $options['template'] : $this->subjectTemplate;
        $id = isset($options['id']) ? $options['id'] : 'subject';

        $classes = ['table', 'table-striped', 'table-hover'];
        if (isset($options['class'])) {
            $classes = array_unique(array_merge($classes, explode(' ', $options['class'])));
        }

        return $this->templating->render($template, [
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
    public function renderSubjectStockUnits(StockSubjectInterface $subject, array $options = [])
    {
        $template = isset($options['template']) ? $options['template'] : $this->unitTemplate;
        $prefix = isset($options['prefix']) ? $options['prefix'] : 'stockUnits';
        $id = isset($options['id']) ? $options['id'] : $prefix . '_' . uniqid();
        $script = isset($options['script']) ? (bool)$options['script'] : false;

        $manual = $subject->getStockMode() === StockSubjectModes::MODE_MANUAL;

        $classes = ['table', 'table-striped', 'table-hover'];
        if (isset($options['class'])) {
            $classes = array_unique(array_merge($classes, explode(' ', $options['class'])));
        }

        $normalized = $this->normalizer->normalize($subject, 'json', ['groups' => ['StockView']]);

        return $this->templating->render($template, [
            'stockUnits' => $normalized['stock_units'],
            'prefix'     => $prefix,
            'id'         => $id,
            'classes'    => implode(' ', $classes),
            'manual'     => $manual,
            'script'     => $script,
        ]);
    }
}
