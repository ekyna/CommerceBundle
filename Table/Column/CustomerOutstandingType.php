<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomerOutstandingType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerOutstandingType extends AbstractColumnType
{
    use FormatterAwareTrait;

    /**
     * @var string
     */
    private $defaultCurrency;


    /**
     * Constructor.
     *
     * @param FormatterFactory $formatterFactory
     * @param string           $defaultCurrency
     */
    public function __construct(FormatterFactory $formatterFactory, string $defaultCurrency)
    {
        $this->formatterFactory = $formatterFactory;
        $this->defaultCurrency = $defaultCurrency;
    }


    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options)
    {
        $formatter = $this->formatterFactory->create(null, $this->defaultCurrency);

        if (0 > $current = $row->getData('outstandingBalance')) {
            $current = -$current;
        }
        $limit = $row->getData('outstandingLimit');

        $current = $formatter->currency($current, $this->defaultCurrency);
        $limit = $formatter->currency($limit, $this->defaultCurrency);

        $view->vars['value'] = sprintf("%s&nbsp;/&nbsp;%s", $current, $limit);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'ekyna_commerce.customer.field.outstanding_balance',
        ]);
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
