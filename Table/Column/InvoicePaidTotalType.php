<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Component\Commerce\Common\Currency\CurrencyRendererInterface;
use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Payment\Resolver\DueDateResolverInterface;
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
     * @var CurrencyRendererInterface
     */
    private $renderer;

    /**
     * @var DueDateResolverInterface
     */
    private $resolver;


    /**
     * Constructor.
     *
     * @param FormatterFactory $formatterFactory
     * @param CurrencyRendererInterface $renderer
     * @param DueDateResolverInterface $resolver
     */
    public function __construct(
        FormatterFactory $formatterFactory,
        CurrencyRendererInterface $renderer,
        DueDateResolverInterface $resolver
    ) {
        $this->formatterFactory = $formatterFactory;
        $this->renderer = $renderer;
        $this->resolver = $resolver;
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

        $view->vars['value'] = $this->getFormatter()->currency($invoice->getPaidTotal(), $invoice->getCurrency());
        //$view->vars['value'] = $this->renderer->renderQuote($invoice->getPaidTotal(), $invoice->getOrder(), false);

        if ($this->resolver->isInvoiceDue($invoice)) {
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
