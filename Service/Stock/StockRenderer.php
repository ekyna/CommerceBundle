<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Stock;

use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
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
    private $subjectTemplate;


    /**
     * Constructor.
     *
     * @param StockUnitResolverInterface $resolver
     * @param EngineInterface            $templating
     * @param string                     $unitTemplate
     * @param string                     $subjectTemplate
     */
    public function __construct(
        StockUnitResolverInterface $resolver,
        EngineInterface $templating,
        $unitTemplate = 'EkynaCommerceBundle:Admin/Stock:stock_units.html.twig',
        $subjectTemplate = 'EkynaCommerceBundle:Admin/Stock:subjects_stock.html.twig'
    ) {
        $this->resolver = $resolver;
        $this->templating = $templating;
        $this->unitTemplate = $unitTemplate;
        $this->subjectTemplate = $subjectTemplate;
    }

    /**
     * Renders the stock units list.
     *
     * @param StockUnitInterface[] $stockUnits
     * @param array                $options
     *
     * @return string
     */
    public function renderStockUnits(array $stockUnits, array $options = [])
    {
        $template = isset($options['template']) ? $options['template'] : $this->unitTemplate;
        $id = isset($options['id']) ? $options['id'] : 'stockUnit';

        $classes = ['table', 'table-striped', 'table-hover'];
        if (isset($options['class'])) {
            $classes = array_unique(array_merge($classes, explode(' ', $options['class'])));
        }

        return $this->templating->render($template, [
            'stockUnits' => $stockUnits,
            'prefix'     => $id,
            'classes'    => implode(' ', $classes),
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
        $stockUnits = $this->resolver->findNotClosed($subject);

        return $this->renderStockUnits($stockUnits, $options);
    }
}
