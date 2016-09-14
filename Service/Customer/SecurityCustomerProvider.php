<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Customer;

use Ekyna\Bundle\CommerceBundle\Repository\CustomerRepository;
use Ekyna\Component\Commerce\Customer\Provider\AbstractCustomerProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class SecurityCustomerProvider
 * @package Ekyna\Bundle\CommerceBundle\Service\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SecurityCustomerProvider extends AbstractCustomerProvider
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;


    /**
     * Constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage, CustomerRepository $customerRepository)
    {
        $this->tokenStorage = $tokenStorage;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritdoc
     */
    public function hasCustomer()
    {
        if (!parent::hasCustomer()) {
            if (null === $token = $this->tokenStorage->getToken()) {
                return false;
            }

            if (null === $user = $token->getUser()) {
                return false;
            }

            if (null !== $this->customer = $this->customerRepository->findOneByUser($user)) {
                return true;
            }
        }

        return false;
    }
}
