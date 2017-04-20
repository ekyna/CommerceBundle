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

use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class CustomerOutstandingType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerOutstandingType extends AbstractColumnType
{
    use FormatterAwareTrait;

    private string $defaultCurrency;

    public function __construct(FormatterFactory $formatterFactory, string $defaultCurrency)
    {
        $this->formatterFactory = $formatterFactory;
        $this->defaultCurrency = $defaultCurrency;
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $formatter = $this->formatterFactory->create(null, $this->defaultCurrency);

        if (0 > $current = $row->getData('outstandingBalance')) {
            $current = -$current;
        }
        $limit = $row->getData('outstandingLimit');

        $current = $formatter->currency($current, $this->defaultCurrency);
        $limit = $formatter->currency($limit, $this->defaultCurrency);

        $view->vars['value'] = sprintf('%s&nbsp;/&nbsp;%s', $current, $limit);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => t('customer.field.outstanding_balance', [], 'EkynaCommerce'),
        ]);
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
