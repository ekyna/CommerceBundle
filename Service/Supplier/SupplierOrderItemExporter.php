<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Supplier;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Supplier\Export\SupplierOrderItemExporter as BaseExporter;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderItemRepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SupplierOrderItemExporter
 * @package Ekyna\Bundle\CommerceBundle\Service\Supplier
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemExporter extends BaseExporter
{
    protected TranslatorInterface $translator;

    public function __construct(
        SupplierOrderItemRepositoryInterface $itemRepository,
        CurrencyConverterInterface           $currencyConverter,
        TranslatorInterface                  $translator
    ) {
        parent::__construct($itemRepository, $currencyConverter);

        $this->translator = $translator;
    }
}
