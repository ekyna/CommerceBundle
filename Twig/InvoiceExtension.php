<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;

/**
 * Class InvoiceExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceExtension extends \Twig_Extension
{
    /**
     * @var ConstantsHelper
     */
    private $constantHelper;

    /**
     * @var TaxResolverInterface
     */
    private $taxResolver;


    /**
     * Constructor.
     *
     * @param ConstantsHelper      $constantHelper
     * @param TaxResolverInterface $taxResolver
     */
    public function __construct(ConstantsHelper $constantHelper, TaxResolverInterface $taxResolver)
    {
        $this->constantHelper = $constantHelper;
        $this->taxResolver = $taxResolver;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'invoice_type_label',
                [$this->constantHelper, 'renderInvoiceTypeLabel'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'invoice_type_badge',
                [$this->constantHelper, 'renderInvoiceTypeBadge'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'invoice_state_label',
                [$this->constantHelper, 'renderInvoiceStateLabel'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'invoice_state_badge',
                [$this->constantHelper, 'renderInvoiceStateBadge'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'invoice_notices',
                [$this, 'renderInvoiceNotices'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getTests()
    {
        return [
            new \Twig_SimpleTest('invoice_subject', function ($subject) {
                return $subject instanceof InvoiceSubjectInterface;
            }),
            new \Twig_SimpleTest(
                'invoice_invoice',
                [InvoiceTypes::class, 'isInvoice']
            ),
            new \Twig_SimpleTest(
                'invoice_credit',
                [InvoiceTypes::class, 'isCredit']
            ),
        ];
    }

    /**
     * Renders the invoice notices.
     *
     * @param InvoiceInterface $invoice
     *
     * @return string
     */
    public function renderInvoiceNotices(InvoiceInterface $invoice)
    {
        if (null !== $rule = $this->taxResolver->resolveSaleTaxRule($invoice->getSale())) {
            return '<p class="text-right">' . implode('<br>', $rule->getNotices()) . '</p>';
        }

        return '';
    }
}
