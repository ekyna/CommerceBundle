<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

use function sprintf;

/**
 * Class SupplierOrderPaymentType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderPaymentType extends AbstractColumnType
{
    use FormatterAwareTrait;

    private TranslatorInterface $translator;


    public function __construct(FormatterFactory $formatterFactory, TranslatorInterface $translator)
    {
        $this->formatterFactory = $formatterFactory;
        $this->translator = $translator;
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $date = $row->getData($options['prefix'] . 'Date');

        $formatter = $this->getFormatter();

        if (null !== $date) {
            $label = $formatter->date($date);
            $class = 'success';
        } elseif ($options['prefix'] === 'forwarder' && null === $row->getData('carrier')) {
            $label = $this->translator->trans('value.none', [], 'EkynaUi');
            $class = 'default';
        } else {
            $label = $this->translator->trans('value.no', [], 'EkynaUi');
            $class = 'danger';

            if (null !== $due = $row->getData($options['prefix'] . 'DueDate')) {
                $label .= '&nbsp;' . $formatter->date($due);
            }
        }

        $view->vars['value'] = sprintf('<span class="label label-%s">%s</span>', $class, $label);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('prefix', null)
            ->setAllowedValues('prefix', ['payment', 'forwarder']);
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
