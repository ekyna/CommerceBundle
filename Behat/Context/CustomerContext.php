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
class CustomerContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following customers:
     *
     * @param TableNode $table
     */
    public function createCustomers(TableNode $table)
    {
        $customers = $this->castCustomersTable($table);

        $manager = $this->getContainer()->get('ekyna_commerce.customer.manager');

        foreach ($customers as $customer) {
            $manager->persist($customer);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @Given /^The customer "(?P<email>[^"]+)" has an outstanding limit of "(?P<limit>[^"]+)"$/
     *
     * @param string    $email
     * @param string    $limit
     */
    public function setOutstandingLimit($email, $limit)
    {
        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $customer */
        $customer = $this->getContainer()
            ->get('ekyna_commerce.customer.repository')
            ->findOneBy(['email' => $email]);

        if (null === $customer) {
            throw new \InvalidArgumentException("Customer with email '$email' not found.");
        }

        $customer->setOutstandingLimit($limit);

        $manager = $this->getContainer()->get('ekyna_commerce.customer.manager');

        $manager->persist($customer);
        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castCustomersTable(TableNode $table)
    {
        $groupRepository = $this->getContainer()->get('ekyna_commerce.customer_group.repository');
        $customerRepository = $this->getContainer()->get('ekyna_commerce.customer.repository');

        $customers = [];
        foreach ($table as $row) {
            /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $customer */
            $customer = $customerRepository->createNew();
            $customer
                ->setEmail($row['email'])
                ->setCompany($row['company'])
                ->setGender($row['gender'])
                ->setLastName($row['lastName'])
                ->setFirstName($row['firstName']);

            // Phone / Mobile
            if (isset($row['phone']) && 0 < strlen($phone = $row['phone'])) {
                $customer->setPhone($phone);
            }
            if (isset($row['mobile']) && 0 < strlen($mobile = $row['mobile'])) {
                $customer->setMobile($mobile);
            }

            // Parent
            if (isset($row['parent']) && 0 < strlen($id = $row['parent'])) {
                if (null === $parent = $customerRepository->find($id)) {
                    throw new \InvalidArgumentException("Failed to find the parent customer with id '{$id}'.");
                }
                $customer->setParent($parent);
            }

            // Customer group
            if (isset($row['group']) && 0 < strlen($name = $row['group'])) {
                if (null === $group = $groupRepository->findOneByName($name)) {
                    throw new \InvalidArgumentException("Failed to find the customer group with name '{$name}'.");
                }
                $customer->setCustomerGroup($group);
            } else {
                $customer->setCustomerGroup($groupRepository->findDefault());
            }

            if (isset($row['osLimit'])) {
                $customer->setOutstandingLimit($row['osLimit']);
            }

            $customers[] = $customer;
        }

        return $customers;
    }
}
