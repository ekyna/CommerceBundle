<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnBuilderInterface;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class SupplierOrderTrackingType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderTrackingType extends AbstractColumnType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function buildColumn(ColumnBuilderInterface $builder, array $options)
    {
        $builder->setSortable(false);
    }

    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options)
    {
        if (!empty($urls = $row->getData('trackingUrls'))) {
            if (false !== $url = end($urls)) {
                $label = $this->translator->trans('ekyna_core.value.yes');

                $view->vars['value'] = '<a href="' . $url . '" target="_blank" class="label label-success">' .
                    $label . '&nbsp;<span class="fa fa-map-marker"></span>' .
                '</a>';

                return;
            }
        }

        $view->vars['value'] = sprintf(
            '<span class="label label-danger">%s</span>',
            $this->translator->trans('ekyna_core.value.no')
        );
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'text';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return PropertyType::class;
    }
}
