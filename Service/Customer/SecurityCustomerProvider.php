<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Customer;

use Ekyna\Bundle\CommerceBundle\Repository\CustomerRepository;
use Ekyna\Bundle\UserBundle\Service\Provider\UserProviderInterface;
use Ekyna\Component\Commerce\Customer\Provider\AbstractCustomerProvider;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;

/**
 * Class SecurityCustomerProvider
 * @package Ekyna\Bundle\CommerceBundle\Service\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SecurityCustomerProvider extends AbstractCustomerProvider
{
    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var UserProviderInterface
     */
    protected $userProvider;

    /**
     * @var bool
     */
    private $initialized = false;


    /**
     * Constructor.
     *
     * @param CustomerGroupRepositoryInterface $customerGroupRepository
     * @param CustomerRepository               $customerRepository
     * @param UserProviderInterface            $userProvider
     */
    public function __construct(
        CustomerGroupRepositoryInterface $customerGroupRepository,
        CustomerRepository $customerRepository,
        UserProviderInterface $userProvider
    ) {
        parent::__construct($customerGroupRepository);

        $this->customerRepository = $customerRepository;
        $this->userProvider = $userProvider;
    }

    /**
     * @inheritdoc
     */
    public function hasCustomer()
    {
        $this->initialize();

        return parent::hasCustomer();
    }

    /**
     * @inheritdoc
     */
    public function getCustomer()
    {
        $this->initialize();

        return parent::getCustomer();
    }

    /**
     * @inheritdoc
     */
    public function reset()
    {
        parent::reset();

        $this->initialized = false;
    }

    /**
     * Loads the customer once.
     */
    private function initialize()
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
