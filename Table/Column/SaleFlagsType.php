<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\CommerceBundle\Service\Common\FlagRenderer;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnBuilderInterface;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\ColumnType;
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
    public function __construct(
        private readonly FlagRenderer $renderer
    ) {
    }

    public function buildColumn(ColumnBuilderInterface $builder, array $options): void
    {
        $builder->setSortable(false);
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $sale = $row->getData($column->getConfig()->getPropertyPath());
        if (!$sale instanceof SaleInterface) {
            throw new UnexpectedTypeException($sale, SaleInterface::class);
        }

        $view->vars['value'] = $this
            ->renderer
            ->renderSaleFlags($sale, ['badge' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('label', null);
    }

    public function getBlockPrefix(): string
    {
        return 'text';
    }

    public function getParent(): ?string
    {
        return ColumnType::class;
    }
}
