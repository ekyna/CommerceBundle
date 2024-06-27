<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Common\FlagRenderer;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnBuilderInterface;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\ColumnType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;

/**
 * Class CustomerFlagsType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerFlagsType extends AbstractColumnType
{
    public function __construct(
        private readonly FlagRenderer $renderer,
    ) {
    }

    public function buildColumn(ColumnBuilderInterface $builder, array $options): void
    {
        $builder
            ->setSortable(false)
            ->setExportable(false);
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $customer = $row->getData($column->getConfig()->getPropertyPath());
        if (!$customer instanceof CustomerInterface) {
            throw new UnexpectedTypeException($customer, CustomerInterface::class);
        }

        $view->vars['value'] = $this
            ->renderer
            ->renderCustomerFlags($customer, ['badge' => false]);
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
