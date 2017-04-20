<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Controller\Account;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Provider\CustomerProviderInterface;
use Ekyna\Component\Commerce\Exception\LogicException;

/**
 * Trait CustomerTrait
 * @package Ekyna\Bundle\CommerceBundle\Controller\Account
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait CustomerTrait
{
    private CustomerProviderInterface $customerProvider;

    public function setCustomerProvider(CustomerProviderInterface $customerProvider): void
    {
        $this->customerProvider = $customerProvider;
    }

    protected function getCustomer(): CustomerInterface
    {
        $customer = $this->customerProvider->getCustomer();

        if (!$customer instanceof CustomerInterface) {
            throw new LogicException(static::class . '::getCustomer() must return the current customer.');
        }

        return $customer;
    }
}
