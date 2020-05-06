<?php

namespace Ekyna\Bundle\CommerceBundle\DataFixtures\ORM;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\CoreBundle\DataFixtures\ORM\Fixtures;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\IdentityInterface;
use Ekyna\Component\Commerce\Common\Repository\CountryRepositoryInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Entity\CustomerAddress;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerAddressRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerGroupRepositoryInterface;
use Ekyna\Component\Commerce\Order\Entity\OrderAddress;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Pricing\Repository\TaxGroupRepositoryInterface;
use Ekyna\Component\Commerce\Stock\Model\WarehouseInterface;
use Ekyna\Component\Commerce\Stock\Repository\WarehouseRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Entity\SubjectIdentity;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierAddress;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;

/**
 * Class CommerceProvider
 * @package Ekyna\Bundle\CommerceBundle\DataFixtures\ORM
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CommerceProvider
{
    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    /**
     * @var CurrencyRepositoryInterface
     */
    private $currencyRepository;

    /**
     * @var TaxGroupRepositoryInterface
     */
    private $taxGroupRepository;

    /**
     * @var CustomerGroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * @var CustomerAddressRepositoryInterface
     */
    private $customerAddressRepository;

    /**
     * @var WarehouseRepositoryInterface
     */
    private $warehouseRepository;

    /**
     * @var SubjectProviderRegistryInterface
     */
    private $providerRegistry;

    /**
     * @var CustomerGroupInterface[]
     */
    private $customerGroups;


    /**
     * Constructor.
     *
     * @param CountryRepositoryInterface         $countryRepository
     * @param CurrencyRepositoryInterface        $currencyRepository
     * @param TaxGroupRepositoryInterface        $taxGroupRepository
     * @param CustomerGroupRepositoryInterface   $customerGroupRepository
     * @param CustomerAddressRepositoryInterface $customerAddressRepository
     * @param WarehouseRepositoryInterface       $warehouseRepository
     * @param SubjectProviderRegistryInterface   $providerRegistry
     */
    public function __construct(
        CountryRepositoryInterface $countryRepository,
        CurrencyRepositoryInterface $currencyRepository,
        TaxGroupRepositoryInterface $taxGroupRepository,
        CustomerGroupRepositoryInterface $customerGroupRepository,
        CustomerAddressRepositoryInterface $customerAddressRepository,
        WarehouseRepositoryInterface $warehouseRepository,
        SubjectProviderRegistryInterface $providerRegistry
    ) {
        $this->countryRepository = $countryRepository;
        $this->currencyRepository = $currencyRepository;
        $this->taxGroupRepository = $taxGroupRepository;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->customerAddressRepository = $customerAddressRepository;
        $this->warehouseRepository = $warehouseRepository;

        $this->providerRegistry = $providerRegistry;
    }

    /**
     * Returns the default tax group.
     *
     * @return TaxGroupInterface
     */
    public function defaultTaxGroup(): TaxGroupInterface
    {
        return $this->taxGroupRepository->findDefault();
    }

    /**
     * Returns the tax group by its code.
     *
     * @param string $code
     *
     * @return TaxGroupInterface
     */
    public function taxGroupByCode(string $code): TaxGroupInterface
    {
        return $this->taxGroupRepository->findOneByCode($code);
    }

    /**
     * Returns the country by its code.
     *
     * @param string $code
     *
     * @return CountryInterface|null
     */
    public function countryByCode(string $code): ?CountryInterface
    {
        return $this->countryRepository->findOneByCode($code);
    }

    /**
     * Returns the currency by its code.
     *
     * @param string $code
     *
     * @return CurrencyInterface|null
     */
    public function currencyByCode(string $code): ?CurrencyInterface
    {
        return $this->currencyRepository->findOneByCode($code);
    }

    /**
     * Finds the customer group by business.
     *
     * @param bool $business
     *
     * @return CustomerGroupInterface
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

        throw new \RuntimeException('Customer group not found.');
    }

    /**
     * Returns the default warehouse repository.
     *
     * @return WarehouseInterface
     */
    public function defaultWarehouse(): WarehouseInterface
    {
        return $this->warehouseRepository->findDefault();
    }

    /**
     * Generates an address.
     *
     * @param IdentityInterface $owner
     * @param null|bool         $ownerIdentity
     *
     * @return OrderAddress|CustomerAddress
     */
    public function generateAddress(IdentityInterface $owner, $ownerIdentity = null)
    {
        $faker = Fixtures::getFaker('fr_FR');

        // TODO use sale factory

        if ($owner instanceof OrderInterface) {
            $address = new OrderAddress();
        } elseif ($owner instanceof CustomerInterface) {
            $address = new CustomerAddress();
            $address->setCustomer($owner);
        } elseif ($owner instanceof SupplierInterface) {
            $address = new SupplierAddress();
            $address->setSupplier($owner);
        } else {
            throw new \InvalidArgumentException('Unexpected owner.');
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
            $address->setPhone(Fixtures::getPhoneUtil()->parse($faker->phoneNumber, 'FR'));
        }
        if (50 < rand(0, 100)) {
            $address->setMobile(Fixtures::getPhoneUtil()->parse($faker->phoneNumber, 'FR'));
        }

        return $address;
    }

    /**
     * Returns the subject identity.
     *
     * @param SubjectInterface $subject
     *
     * @return SubjectIdentity
     *
     * @throws \Exception
     */
    public function subjectIdentity(SubjectInterface $subject)
    {
        $provider = $this->providerRegistry->getProviderBySubject($subject);
        if (null === $provider) {
            throw new \Exception('Unsupported subject');
        }

        $identity = new SubjectIdentity();

        $provider->transform($subject, $identity);

        return $identity;
    }
}
