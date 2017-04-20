<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Factory;

use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Factory\AbstractSaleFactory as BaseFactory;
use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Updater\SaleUpdaterInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerRepositoryInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class AbstractSaleFactory
 * @package Ekyna\Bundle\CommerceBundle\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractSaleFactory extends BaseFactory
{
    private RequestStack                $requestStack;
    private CustomerRepositoryInterface $customerRepository;

    public function __construct(
        SaleFactoryInterface $saleFactory,
        SaleUpdaterInterface $saleUpdater,
        LocaleProviderInterface $localeProvider,
        CurrencyProviderInterface $currencyProvider,
        RequestStack                $requestStack,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($saleFactory, $saleUpdater, $localeProvider, $currencyProvider);

        $this->requestStack = $requestStack;
        $this->customerRepository = $customerRepository;
    }

    protected function initialize(SaleInterface $sale): void
    {
        $this->loadCustomerFromRequest($sale);

        parent::initialize($sale);
    }

    private function loadCustomerFromRequest(SaleInterface $sale): void
    {
        if (!$request = $this->requestStack->getMainRequest()) {
            return;
        }

        if (0 >= $customerId = $request->query->getInt('customer')) {
            return;
        }

        if (!$customer = $this->customerRepository->find($customerId)) {
            return;
        }

        $sale->setCustomer($customer);

        // TODO Addresses
    }
}
