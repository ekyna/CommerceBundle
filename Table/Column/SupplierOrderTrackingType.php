<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnBuilderInterface;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Contracts\Translation\TranslatorInterface;

use function end;
use function sprintf;

/**
 * Class SupplierOrderTrackingType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderTrackingType extends AbstractColumnType
{
    private TranslatorInterface $translator;


    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildColumn(ColumnBuilderInterface $builder, array $options): void
    {
        $builder->setSortable(false);
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        if (!empty($urls = $row->getData('trackingUrls')) && (false !== $url = end($urls))) {
            $label = $this->translator->trans('value.yes', [], 'EkynaUi');

            $view->vars['value'] = '<a href="' . $url . '" target="_blank" class="label label-success">' .
                $label . '&nbsp;<span class="fa fa-map-marker"></span>' .
            '</a>';

            return;
        }

        $view->vars['value'] = sprintf(
            '<span class="label label-danger">%s</span>',
            $this->translator->trans('value.no', [], 'EkynaUi')
        );
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
