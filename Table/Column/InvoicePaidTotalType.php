<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Component\Commerce\Common\Currency\CurrencyRendererInterface;
use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Payment\Resolver\DueDateResolverInterface;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;

use function trim;

/**
 * Class InvoicePaidTotalType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoicePaidTotalType extends AbstractColumnType
{
    use FormatterAwareTrait;

    private CurrencyRendererInterface $renderer;
    private DueDateResolverInterface $resolver;

    public function __construct(
        FormatterFactory $formatterFactory,
        CurrencyRendererInterface $renderer,
        DueDateResolverInterface $resolver
    ) {
        $this->formatterFactory = $formatterFactory;
        $this->renderer = $renderer;
        $this->resolver = $resolver;
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $invoice = $row->getData(null);

        if (!$invoice instanceof OrderInvoiceInterface) {
            throw new UnexpectedTypeException($invoice, OrderInvoiceInterface::class);
        }

        $view->vars['value'] = $this->getFormatter()->currency($invoice->getPaidTotal(), $invoice->getCurrency());
        //$view->vars['value'] = $this->renderer->renderQuote($invoice->getPaidTotal(), $invoice->getOrder(), false);

        if ($this->resolver->isInvoiceDue($invoice)) {
            $class = $view->vars['attr']['class'] ?? '';
            $view->vars['attr']['class'] = trim($class . ' danger');
        }
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
