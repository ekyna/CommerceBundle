<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;

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
     * Constructor.
     *
     * @param ConstantsHelper $constantHelper
     */
    public function __construct(ConstantsHelper $constantHelper)
    {
        $this->constantHelper = $constantHelper;
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
        ];
    }

    /**
     * @inheritDoc
     */
    public function getTests()
    {
        return [
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
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_commerce_invoice';
    }
}
