<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Factory;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Common\InChargeResolver;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Factory\CustomerFactory as BaseFactory;
use Ekyna\Component\Commerce\Common\Currency\CurrencyProviderInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class CustomerFactory
 * @package Ekyna\Bundle\CommerceBundle\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerFactory extends BaseFactory implements CustomerFactoryInterface
{
    protected InChargeResolver $inChargeResolver;

    public function __construct(
        CurrencyProviderInterface        $currencyProvider,
        LocaleProviderInterface          $localeProvider,
        CustomerGroupRepositoryInterface $customerGroupRepository,
        InChargeResolver                 $inChargeResolver
    ) {
        parent::__construct($currencyProvider, $localeProvider, $customerGroupRepository);

        $this->inChargeResolver = $inChargeResolver;
    }

    /**
     * @return CustomerInterface
     */
    public function create(): ResourceInterface
    {
        $customer = parent::create();

        $this->inChargeResolver->update($customer);

        return $customer;
    }

    public function createWithUser(UserInterface $user): CustomerInterface
    {
        $customer = $this->create();

        $customer
            ->setUser($user)
            ->setEmail($user->getEmail());

        return $customer;
    }
}
