<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\DataFixtures\ORM;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\IdentityInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Commerce\Order\Model\OrderAddressInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Pricing\Repository\TaxGroupRepositoryInterface;
use Ekyna\Component\Commerce\Stock\Model\WarehouseInterface;
use Ekyna\Component\Commerce\Stock\Repository\WarehouseRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierAddressInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Exception;
use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;
use libphonenumber\PhoneNumberUtil;
use RuntimeException;

/**
 * Class CommerceProvider
 * @package Ekyna\Bundle\CommerceBundle\DataFixtures\ORM
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CommerceProvider
{
    private CountryRepositoryInterface       $countryRepository;
    private CurrencyRepositoryInterface      $currencyRepository;
    private TaxGroupRepositoryInterface      $taxGroupRepository;
    private CustomerGroupRepositoryInterface $customerGroupRepository;
    private WarehouseRepositoryInterface     $warehouseRepository;
    private SubjectProviderRegistryInterface $providerRegistry;
    private FactoryFactoryInterface          $factoryFactory;

    /** @var array<CustomerGroupInterface> */
    private ?array           $customerGroups = null;
    private ?Generator       $faker          = null;
    private ?PhoneNumberUtil $phoneUtil      = null;

    public function __construct(
        CountryRepositoryInterface       $countryRepository,
        CurrencyRepositoryInterface      $currencyRepository,
        TaxGroupRepositoryInterface      $taxGroupRepository,
        CustomerGroupRepositoryInterface $customerGroupRepository,
        WarehouseRepositoryInterface     $warehouseRepository,
        SubjectProviderRegistryInterface $providerRegistry,
        FactoryFactoryInterface          $factoryFactory
    ) {
        $this->countryRepository = $countryRepository;
        $this->currencyRepository = $currencyRepository;
        $this->taxGroupRepository = $taxGroupRepository;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->warehouseRepository = $warehouseRepository;
        $this->providerRegistry = $providerRegistry;
        $this->factoryFactory = $factoryFactory;
    }

    /**
     * Returns the default tax group.
     */
    public function defaultTaxGroup(): TaxGroupInterface
    {
        return $this->taxGroupRepository->findDefault();
    }

    /**
     * Returns the tax group by its code.
     */
    public function taxGroupByCode(string $code): TaxGroupInterface
    {
        return $this->taxGroupRepository->findOneByCode($code);
    }

    /**
     * Returns the country by its code.
     */
    public function countryByCode(string $code): ?CountryInterface
    {
        return $this->countryRepository->findOneByCode($code);
    }

    /**
     * Returns the currency by its code.
     */
    public function currencyByCode(string $code): ?CurrencyInterface
    {
        return $this->currencyRepository->findOneByCode($code);
    }

    /**
     * Finds the customer group by business.
     */
    public function customerGroup(bool $business = false): CustomerGroupInterface
    {
        if (null === $this->customerGroups) {
            $this->customerGroups = $this->customerGroupRepository->findAll();
        }

        foreach ($this->customerGroups as $group) {
            if ($business === $group->isBusiness()) {
                return $group;
            }
        }

        throw new RuntimeException('Customer group not found.');
    }

    /**
     * Returns the default warehouse.
     */
    public function defaultWarehouse(): WarehouseInterface
    {
        return $this->warehouseRepository->findDefault();
    }

    /**
     * Generates an address.
     *
     * @return OrderAddressInterface|CustomerAddressInterface|SupplierAddressInterface
     */
    public function generateAddress(IdentityInterface $owner, ?bool $ownerIdentity = null): AddressInterface
    {
        $faker = $this->getFaker();

        if ($owner instanceof OrderInterface) {
            /** @var OrderAddressInterface $address */
            $address = $this->factoryFactory->getFactory(OrderAddressInterface::class)->create();
        } elseif ($owner instanceof CustomerInterface) {
            /** @var CustomerAddressInterface $address */
            $address = $this->factoryFactory->getFactory(CustomerAddressInterface::class)->create();
            $address->setCustomer($owner);
        } elseif ($owner instanceof SupplierInterface) {
            /** @var SupplierAddressInterface $address */
            $address = $this->factoryFactory->getFactory(SupplierAddressInterface::class)->create();
            $address->setSupplier($owner);
        } else {
            throw new InvalidArgumentException('Unexpected owner.');
        }

        if (false !== $ownerIdentity && ($ownerIdentity || 50 < rand(0, 100))) {
            $address
                ->setGender($owner->getGender())
                ->setFirstName($owner->getFirstName())
                ->setLastName($owner->getLastName());
        } else {
            $address
                ->setGender('mr')
                ->setFirstName($faker->firstName)
                ->setLastName($faker->lastName);
        }

        $address
            ->setStreet($faker->streetAddress)
            ->setPostalCode(str_replace(' ', '', $faker->postcode))
            ->setCity($faker->city)
            ->setCountry($this->countryByCode('FR'));

        if (50 < rand(0, 100)) {
            $address->setPhone($this->getPhoneUtil()->parse($faker->phoneNumber, 'FR'));
        }
        if (50 < rand(0, 100)) {
            $address->setMobile($this->getPhoneUtil()->parse($faker->phoneNumber, 'FR'));
        }

        return $address;
    }

    /**
     * Returns the subject identity.
     *
     * @throws Exception
     */
    public function subjectIdentity(SubjectInterface $subject): SubjectIdentity
    {
        $identity = new SubjectIdentity();
        $identity
            ->setProvider($subject::getProviderName())
            ->setIdentifier($subject->getIdentifier())
            ->setSubject($subject);

        return $identity;
    }

    private function getFaker(): Generator
    {
        if ($this->faker) {
            return $this->faker;
        }

        return $this->faker = Factory::create();
    }

    private function getPhoneUtil(): PhoneNumberUtil
    {
        if ($this->phoneUtil) {
            return $this->phoneUtil;
        }

        return $this->phoneUtil = PhoneNumberUtil::getInstance();
    }
}
