<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Invoice;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoicePaymentResolverInterface;
use Ekyna\Component\Commerce\Payment\Resolver\DueDateResolverInterface;

/**
 * Class InvoiceHelper
 * @package Ekyna\Bundle\CommerceBundle\Service\Invoice
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceHelper
{
    private DueDateResolverInterface        $dueDateResolver;
    private InvoicePaymentResolverInterface $paymentResolver;


    public function __construct(
        DueDateResolverInterface $dueDateResolver,
        InvoicePaymentResolverInterface $paymentResolver
    ) {
        $this->dueDateResolver = $dueDateResolver;
        $this->paymentResolver = $paymentResolver;
    }

    /**
     * @see InvoicePaymentResolverInterface::resolve()
     */
    public function getInvoicePayments(InvoiceInterface $invoice, bool $invoices = true): array
    {
        return $this->paymentResolver->resolve($invoice, $invoices);
    }

    /**
     * @see InvoicePaymentResolverInterface::getPaidTotal()
     */
    public function getInvoicePaidTotal(InvoiceInterface $invoice): Decimal
    {
        return $this->paymentResolver->getPaidTotal($invoice);
    }

    /**
     * @see DueDateResolverInterface::isInvoiceDue()
     */
    public function isInvoiceDue(InvoiceInterface $invoice): bool
    {
        return $this->dueDateResolver->isInvoiceDue($invoice);
    }
}
