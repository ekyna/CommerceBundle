<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Customer;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Provider\AbstractCustomerProvider;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerRepositoryInterface;
use Ekyna\Component\User\Service\UserProviderInterface;

/**
 * Class SecurityCustomerProvider
 * @package Ekyna\Bundle\CommerceBundle\Service\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SecurityCustomerProvider extends AbstractCustomerProvider
{
    protected CustomerRepositoryInterface $customerRepository;
    protected UserProviderInterface       $userProvider;

    private bool $initialized = false;


    public function __construct(
        CustomerGroupRepositoryInterface $customerGroupRepository,
        CustomerRepositoryInterface $customerRepository,
        UserProviderInterface $userProvider
    ) {
        parent::__construct($customerGroupRepository);

        $this->customerRepository = $customerRepository;
        $this->userProvider = $userProvider;
    }

    /**
     * @inheritDoc
     */
    public function hasCustomer(): bool
    {
        $this->initialize();

        return parent::hasCustomer();
    }

    /**
     * @inheritDoc
     */
    public function getCustomer(): ?CustomerInterface
    {
        $this->initialize();

        return parent::getCustomer();
    }

    /**
     * @inheritDoc
     */
    public function reset(): void
    {
        parent::reset();

        $this->initialized = false;
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        parent::reset();

        $this->initialized = true;
    }

    /**
     * Loads the customer once.
     */
    private function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;

        if (null === $user = $this->userProvider->getUser()) {
            return;
        }

        $this->customer = $this->customerRepository->findOneByUser($user);
    }
}
