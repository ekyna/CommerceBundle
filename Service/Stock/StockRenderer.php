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
    private $defaultTemplate;


    /**
     * Constructor.
     *
     * @param StockUnitResolverInterface $resolver
     * @param EngineInterface            $templating
     * @param string                     $defaultTemplate
     */
    public function __construct(
        StockUnitResolverInterface $resolver,
        EngineInterface $templating,
        $defaultTemplate = 'EkynaCommerceBundle:Admin/Stock:stock_unit_list.html.twig'
    ) {
        $this->resolver = $resolver;
        $this->templating = $templating;
        $this->defaultTemplate = $defaultTemplate;
    }

    /**
     * Renders the stock units list.
     *
     * @param StockUnitInterface[] $stockUnits
     * @param array                $options
     *
     * @return string
     */
    public function renderStockUnitList($stockUnits, array $options = [])
    {
        $template = isset($options['template']) ? $options['template'] : $this->defaultTemplate;
        $id = isset($options['id']) ? $options['id'] : 'stockUnit';

        $classes = ['table', 'table-striped', 'table-hover'];
        if (isset($options['class'])) {
            $classes = array_unique(array_merge($classes, explode(' ', $options['class'])));
        }
        return $this->templating->render($template, [
            'stockUnits' => $stockUnits,
            'prefix'     => $id,
            'classes' => implode(' ', $classes)
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
    public function renderSubjectStockUnitList(StockSubjectInterface $subject, array $options = [])
    {
        $stockUnits = $this->resolver->findNotClosed($subject);

        return $this->renderStockUnitList($stockUnits, $options);
    }
}
