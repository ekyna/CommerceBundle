<?php

namespace Ekyna\Bundle\CommerceBundle\Table\Column;

use Ekyna\Component\Commerce\Common\Util\Formatter;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoicePaymentResolverInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnBuilderInterface;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InvoicePaymentsType
 * @package Ekyna\Bundle\CommerceBundle\Table\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoicePaymentsType extends AbstractColumnType
{
    /**
     * @var InvoicePaymentResolverInterface
     */
    private $paymentResolver;

    /**
     * @var Formatter
     */
    private $formatter;


    /**
     * Constructor.
     *
     * @param InvoicePaymentResolverInterface $paymentResolver
     * @param Formatter                       $formatter
     */
    public function __construct(InvoicePaymentResolverInterface $paymentResolver, Formatter $formatter)
    {
        $this->paymentResolver = $paymentResolver;
        $this->formatter = $formatter;
    }

    /**
     * @inheritdoc
     */
    public function buildColumn(ColumnBuilderInterface $builder, array $options)
    {
        $builder->setSortable(false);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('alignment', 'right');
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

        $payments = $this->paymentResolver->resolve($invoice);
        $total = 0;
        foreach ($payments as $payment) {
            $total += $payment->getAmount();
        }

        $view->vars['value'] = $this->formatter->currency($total, $invoice->getCurrency());

        if (-1 !== Money::compare($total, $invoice->getGrandTotal(), $invoice->getCurrency())) {
            return;
        }

        if (null === $date = $invoice->getSale()->getOutstandingDate()) {
            return;
        }

        $diff = $date->diff((new \DateTime())->setTime(0, 0, 0));
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
