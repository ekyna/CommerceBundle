<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Customer;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Export\InitiatorExporter as BaseExporter;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Quote\Repository\QuoteRepositoryInterface;

/**
 * Class InitiatorExporter
 * @package Ekyna\Bundle\CommerceBundle\Service\Customer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InitiatorExporter extends BaseExporter
{
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        QuoteRepositoryInterface $quoteRepository,
        protected readonly ConstantsHelper $constantsHelper,
    ) {
        parent::__construct($orderRepository, $quoteRepository);
    }

    protected function buildHeaders(): array
    {
        $translator = $this->constantsHelper->getTranslator();

        return [
            $translator->trans('field.number', [], 'EkynaUi'),
            $translator->trans('field.date', [], 'EkynaUi'),
            $translator->trans('field.company', [], 'EkynaUi'),
            $translator->trans('customer.label.singular', [], 'EkynaCommerce'),
            $translator->trans('sale.field.ati_total', [], 'EkynaCommerce'),
            $translator->trans('field.status', [], 'EkynaUi'),
        ];
    }

    protected function buildRow(SaleInterface $sale): array
    {
        $row = parent::buildRow($sale);

        $row['status'] = $this->constantsHelper->renderSaleStateLabel($sale);

        return $row;
    }
}
