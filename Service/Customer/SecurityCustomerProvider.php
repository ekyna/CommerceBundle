<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Customer;

use Ekyna\Bundle\CommerceBundle\Repository\CustomerRepository;
use Ekyna\Component\Commerce\Customer\Provider\AbstractCustomerProvider;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var bool
     */
    private $initialized = false;


    /**
     * Constructor.
     *
     * @param CustomerGroupRepositoryInterface $customerGroupRepository
     * @param CustomerRepository               $customerRepository
     * @param TokenStorageInterface            $tokenStorage
     */
    public function __construct(
        CustomerGroupRepositoryInterface $customerGroupRepository,
        CustomerRepository $customerRepository,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($customerGroupRepository);

        $this->customerRepository = $customerRepository;
        $this->tokenStorage = $tokenStorage;
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
        if (!$this->initialized) {
            if (null === $token = $this->tokenStorage->getToken()) {
                return;
            }

            if (!is_object($user = $token->getUser())) {
                return;
            }

            $this->customer = $this->customerRepository->findOneByUser($user);

            $this->initialized = true;
        }
    }
}
