<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnBuilderInterface;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SupplierOrderPaymentType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderPaymentType extends AbstractColumnType
{
    /**
     * @var Formatter
     */
    private $formatter;


    /**
     * Constructor.
     *
     * @param Formatter $formatter
     */
    public function __construct(Formatter $formatter)
    {
        $this->formatter = $formatter;
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
        $date = $row->getData($options['prefix'] . 'Date');

        if (null !== $date) {
            $label = $this->formatter->date($date);
            $class = 'label-success';
        } else {
            $label = 'No';
            $class = 'label-danger';

            if (null !== $due = $row->getData($options['prefix'] . 'DueDate')) {
                $label.= $this->formatter->date($due);
            }
        }

        $view->vars['label'] = $label;
        $view->vars['class'] = $class;
        $view->vars['route'] = null;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('prefix', null)
            ->setAllowedValues('prefix', ['payment', 'forwarder']);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'boolean';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return PropertyType::class;
    }
}
