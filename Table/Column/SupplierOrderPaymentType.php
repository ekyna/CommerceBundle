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
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * Constructor.
     *
     * @param Formatter           $formatter
     * @param TranslatorInterface $translator
     */
    public function __construct(Formatter $formatter, TranslatorInterface $translator)
    {
        $this->formatter = $formatter;
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
        $date = $row->getData($options['prefix'] . 'Date');

        if (null !== $date) {
            $label = $this->formatter->date($date);
            $class = 'success';
        } elseif ($options['prefix'] === 'forwarder' && null === $row->getData('carrier')) {
            $label = $this->translator->trans('ekyna_core.value.none');
            $class = 'default';
        } else {
            $label = $this->translator->trans('ekyna_core.value.no');
            $class = 'danger';

            if (null !== $due = $row->getData($options['prefix'] . 'DueDate')) {
                $label .= '&nbsp;' . $this->formatter->date($due);
            }
        }

        $view->vars['value'] = sprintf('<span class="label label-%s">%s</span>', $class, $label);
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
