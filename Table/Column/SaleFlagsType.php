<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\CommerceBundle\Service\Common\FlagRenderer;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnBuilderInterface;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SaleFlagsType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleFlagsType extends AbstractColumnType
{
    private FlagRenderer $renderer;


    public function __construct(FlagRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function buildColumn(ColumnBuilderInterface $builder, array $options): void
    {
        $builder->setSortable(false);
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $view->vars['value'] = $this->renderer->renderSaleFlags($view->vars['value'], ['badge' => false]);
        $view->vars['block_prefix'] = 'text';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('label', null);
    }

    public function getParent(): ?string
    {
        return PropertyType::class;
    }
}
