<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class StockSubjectModeType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockSubjectModeType extends AbstractColumnType
{
    private ConstantsHelper $constantHelper;

    public function __construct(ConstantsHelper $constantHelper)
    {
        $this->constantHelper = $constantHelper;
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $view->vars['value'] = $this->constantHelper->renderStockSubjectModeBadge($view->vars['value']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('label', t('stock_subject.field.mode', [], 'EkynaCommerce'));
    }

    public function getBlockPrefix(): string
    {
        return 'text';
    }

    public function getParent(): ?string
    {
        return PropertyType::class;
    }
}
