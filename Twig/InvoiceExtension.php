<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\CommerceBundle\Service\Invoice\InvoiceHelper;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigTest;

/**
 * Class InvoiceExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'invoice_type_label',
                [ConstantsHelper::class, 'renderInvoiceTypeLabel'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'invoice_type_badge',
                [ConstantsHelper::class, 'renderInvoiceTypeBadge'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'invoice_state_label',
                [ConstantsHelper::class, 'renderInvoiceStateLabel'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'invoice_state_badge',
                [ConstantsHelper::class, 'renderInvoiceStateBadge'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'invoice_payments',
                [InvoiceHelper::class, 'getInvoicePayments']
            ),
            new TwigFilter(
                'invoice_paid_total',
                [InvoiceHelper::class, 'getInvoicePaidTotal']
            ),
        ];
    }

    public function getTests(): array
    {
        return [
            new TwigTest('invoice', function ($subject) {
                return $subject instanceof InvoiceInterface;
            }),
            new TwigTest('invoice_subject', function ($subject) {
                return $subject instanceof InvoiceSubjectInterface;
            }),
            new TwigTest(
                'due_invoice',
                [InvoiceHelper::class, 'isInvoiceDue']
            ),
        ];
    }
}
