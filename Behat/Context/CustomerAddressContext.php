<?php

namespace Ekyna\Bundle\CommerceBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class CustomerContext
 * @package Ekyna\Bundle\CommerceBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerAddressContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given /^The customer "(?P<email>[^"]+)" has the following addresses:$/
     *
     * @param string    $email
     * @param TableNode $table
     */
    public function createAddresses($email, TableNode $table)
    {
        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $customer */
        $customer = $this->getContainer()
            ->get('ekyna_commerce.customer.repository')
            ->findOneBy(['email' => $email]);

        if (null === $customer) {
            throw new \InvalidArgumentException("Customer with email '$email' not found.");
        }

        $addresses = $this->castAddressesTable($table);

        $manager = $this->getContainer()->get('ekyna_commerce.customer_address.manager');

        foreach ($addresses as $address) {
            $address->setCustomer($customer);

            $manager->persist($address);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return \Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface[]
     */
    private function castAddressesTable(TableNode $table)
    {
        $countryRepository = $this->getContainer()->get('ekyna_commerce.country.repository');
        $addressRepository = $this->getContainer()->get('ekyna_commerce.customer_address.repository');

        $addresses = [];
        foreach ($table as $row) {
            /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface $address */
            $address = $addressRepository->createNew();
            $address
                ->setCompany($row['company'])
                ->setGender($row['gender'])
                ->setLastName($row['lastName'])
                ->setFirstName($row['firstName'])
                ->setStreet($row['street'])
                ->setPostalCode($row['postalCode'])
                ->setCity($row['city']);

            // Supplement / Phone / Mobile
            if (isset($row['complement']) && 0 < strlen($complement = $row['complement'])) {
                $address->setComplement($complement);
            }
            if (isset($row['supplement']) && 0 < strlen($supplement = $row['supplement'])) {
                $address->setSupplement($supplement);
            }
            if (isset($row['phone']) && 0 < strlen($phone = $row['phone'])) {
                $address->setPhone($phone);
            }
            if (isset($row['mobile']) && 0 < strlen($mobile = $row['mobile'])) {
                $address->setMobile($mobile);
            }

            // Country
            if (isset($row['country']) && 0 < strlen($code = $row['country'])) {
                if (null === $country = $countryRepository->findOneByCode($code)) {
                    throw new \InvalidArgumentException("Failed to find the country with code '{$code}'.");
                }

                $address->setCountry($country);
            }

            $addresses[] = $address;
        }

        return $addresses;
    }
}
