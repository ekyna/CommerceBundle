<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Ekyna\Component\Commerce\Payment\Resolver\DueDateResolverInterface;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
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
    /**
     * @var TaxResolverInterface
     */
    private $taxResolver;

    /**
     * @var DueDateResolverInterface
     */
    private $dueDateResolver;


    /**
     * Constructor.
     *
     * @param TaxResolverInterface     $taxResolver
     * @param DueDateResolverInterface $dueDateResolver
     */
    public function __construct(TaxResolverInterface $taxResolver, DueDateResolverInterface $dueDateResolver)
    {
        $this->taxResolver = $taxResolver;
        $this->dueDateResolver = $dueDateResolver;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
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
                'invoice_notices',
                [$this, 'getInvoiceNotices']
            ),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getTests()
    {
        return [
            new TwigTest('invoice_subject', function ($subject) {
                return $subject instanceof InvoiceSubjectInterface;
            }),
            new TwigTest(
                'invoice_invoice',
                [InvoiceTypes::class, 'isInvoice']
            ),
            new TwigTest(
                'invoice_credit',
                [InvoiceTypes::class, 'isCredit']
            ),
            new TwigTest(
                'due_invoice',
                [$this->dueDateResolver, 'isInvoiceDue']
            ),
        ];
    }

    /**
     * Returns the invoice notices.
     *
     * @param InvoiceInterface $invoice
     *
     * @return string[]
     */
    public function getInvoiceNotices(InvoiceInterface $invoice): array
    {
        $notices = [];

        $locale = $invoice->getLocale();
        $sale = $invoice->getSale();

        if ($rule = $this->taxResolver->resolveSaleTaxRule($sale)) {
            $notices[] = '<p class="text-right">' . implode('<br>', $rule->getNotices()) . '</p>';
        }

        if ($method = $sale->getPaymentMethod()) {
            $translation = $method->translate($locale);

            if (!empty($mention = $translation->getMention())) {
                $notices[] = $mention;
            }
        }

        return $notices;
    }
}
