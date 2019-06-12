<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;

/**
 * Class InvoicePaidTotalType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoicePaidTotalType extends AbstractColumnType
{
    use FormatterAwareTrait;


    /**
     * Constructor.
     *
     * @param FormatterFactory $formatterFactory
     */
    public function __construct(FormatterFactory $formatterFactory)
    {
        $this->formatterFactory = $formatterFactory;
    }

    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options)
    {
        $invoice = $row->getData();

        if (!$invoice instanceof OrderInvoiceInterface) {
            throw new UnexpectedValueException("Expected instance of " . OrderInvoiceInterface::class);
        }

        if ($invoice->getType() === InvoiceTypes::TYPE_CREDIT) {
            $view->vars['value'] = '';

            return;
        }

        $paid = $invoice->getPaidTotal();

        $view->vars['value'] = $this->getFormatter()->currency($paid, $invoice->getCurrency());

        if (1 !== Money::compare($invoice->getGrandTotal(), $paid, $invoice->getCurrency())) {
            return;
        }

        if (null === $date = $invoice->getDueDate()) {
            return;
        }

        $diff = $date->diff((new \DateTime())->setTime(0, 0, 0, 0));
        if (0 < $diff->days && !$diff->invert) {
            $class = $view->vars['attr']['class'] ?? '';
            $view->vars['attr']['class'] = trim($class . ' danger');
        }
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
