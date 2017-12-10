<?php

namespace Ekyna\Bundle\CommerceBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Ekyna\Component\Commerce\Bridge\Payum\Offline\Constants as Offline;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;

/**
 * Class OrderContext
 * @package Ekyna\Bundle\CommerceBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following orders:
     *
     * @param TableNode $table
     */
    public function createOrders(TableNode $table)
    {
        $orders = $this->castOrdersTable($table);

        $manager = $this->getContainer()->get('ekyna_commerce.order.manager');

        foreach ($orders as $order) {
            $manager->persist($order);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @Given /^The order with number "(?P<number>[^"]+)" is paid$/
     *
     * @param string $number
     */
    public function orderIsPaid($number)
    {
        $order = $this->findOrderByNumber($number);

        $methods = $this
            ->getContainer()->get('ekyna_commerce.payment_method.repository')
            ->findBy(['enabled' => true, 'factoryName' => Offline::FACTORY_NAME]);

        if (empty($methods)) {
            throw new \InvalidArgumentException("Failed to find a payment method.");
        }

        $payment = $this
            ->getContainer()
            ->get('ekyna_commerce.sale_factory')
            ->createPaymentForSale($order);

        $payment
            ->setMethod($methods[0])
            ->setState(PaymentStates::STATE_CAPTURED);

        $order->addPayment($payment);

        $this
            ->getContainer()
            ->get('ekyna_commerce.order_payment.operator')
            ->create($payment);

        $this
            ->getContainer()
            ->get('ekyna_commerce.order_payment.manager')
            ->clear();
    }

    /**
     * @Given /^The order with number "(?P<number>[^"]+)" is shipped$/
     *
     * @param string $number
     */
    public function orderIsShipped($number)
    {
        $order = $this->findOrderByNumber($number);

        $methods = $this
            ->getContainer()->get('ekyna_commerce.shipment_method.repository')
            ->findBy(['enabled' => true]);

        if (empty($methods)) {
            throw new \InvalidArgumentException("Failed to find a shipment method.");
        }

        $shipment = $this
            ->getContainer()
            ->get('ekyna_commerce.sale_factory')
            ->createShipmentForSale($order);

        $shipment
            ->setMethod($methods[0])
            ->setState(ShipmentStates::STATE_SHIPPED);

        $order->addShipment($shipment);

        $this
            ->getContainer()
            ->get('ekyna_commerce.shipment.builder')
            ->build($shipment);

        $this
            ->getContainer()
            ->get('ekyna_commerce.order_shipment.operator')
            ->create($shipment);

        $this
            ->getContainer()
            ->get('ekyna_commerce.order_shipment.manager')
            ->clear();
    }

    /**
     * Finds the order by its number.
     *
     * @param string $number
     *
     * @return \Ekyna\Component\Commerce\Order\Model\OrderInterface
     */
    private function findOrderByNumber($number)
    {
        /** @var \Ekyna\Component\Commerce\Order\Model\OrderInterface $order */
        $order = $this
            ->getContainer()
            ->get('ekyna_commerce.order.repository')
            ->findOneBy(['number' => $number]);

        if (null === $order) {
            throw new \InvalidArgumentException("Failed to find order with number '$number'.");
        }

        return $order;
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castOrdersTable(TableNode $table)
    {
        $saleFactory = $this->getContainer()->get('ekyna_commerce.sale_factory');
        $orderRepository = $this->getContainer()->get('ekyna_commerce.order.repository');
        $countryRepository = $this->getContainer()->get('ekyna_commerce.country.repository');
        $currencyRepository = $this->getContainer()->get('ekyna_commerce.currency.repository');
        $shipmentMethodRepository = $this->getContainer()->get('ekyna_commerce.shipment_method.repository');
        $customerRepository = $this->getContainer()->get('ekyna_commerce.customer.repository');
        $customerGroupRepository = $this->getContainer()->get('ekyna_commerce.customer_group.repository');

        $orders = [];
        foreach ($table as $row) {
            /** @var \Ekyna\Component\Commerce\Order\Model\OrderInterface $order */
            $order = $orderRepository->createNew();

            if (isset($row['currency'])) {
                if (null === $currency = $currencyRepository->findOneByCode($row['currency'])) {
                    throw new \InvalidArgumentException("Failed to find the currency with code '{$row['currency']}'.");
                }
                $order->setCurrency($currency);
            } else {
                $order->setCurrency($currencyRepository->findDefault());
            }

            /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $customer */
            $customer = null;
            if (isset($row['customer'])) {
                if (null === $customer = $customerRepository->findOneBy(['email' => $row['customer']])) {
                    throw new \InvalidArgumentException("Failed to find the customer with email '{$row['customer']}'.");
                }
                $order->setCustomer($customer);
            } else {
                if (isset($row['company'])) {
                    $order->setCompany($row['company']);
                }
                $order
                    ->setEmail($row['email'])
                    ->setGender(isset($row['gender']) ? $row['gender'] : 'mr')
                    ->setLastName($row['lastName'])
                    ->setFirstName($row['firstName']);

                if (isset($row['customerGroup'])) {
                    /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface $customerGroup */
                    if (null === $customerGroup = $customerGroupRepository->findOneBy(['name' => $row['name']])) {
                        throw new \InvalidArgumentException("Failed to find the customer with email '{$row['customer']}'.");
                    }
                    $order->setCustomerGroup($customerGroup);
                } else {
                    $order->setCustomerGroup($customerGroupRepository->findDefault());
                }
            }

            if (isset($row['shipmentMethod'])) {
                if (null === $method = $shipmentMethodRepository->findOneBy(['name' => $row['shipmentMethod']])) {
                    throw new \InvalidArgumentException("Failed to find the shipment method with name '{$row['shipmentMethod']}'.");
                }
                $order->setShipmentMethod($method);
            }

            if (isset($row['number'])) {
                $order->setNumber($row['number']);
            }

            // Invoice address
            $country = isset($row['country'])
                ? $countryRepository->findOneByCode($row['country'])
                : $countryRepository->findDefault();

            $invoiceAddress = $saleFactory->createAddressForSale($order);
            $invoiceAddress
                ->setGender($customer ? $customer->getGender() : (isset($row['gender']) ? $row['gender'] : 'mr'))
                ->setLastName($customer ? $customer->getLastName() : $row['lastName'])
                ->setFirstName($customer ? $customer->getFirstName() : $row['firstName'])
                ->setStreet($row['street'])
                ->setPostalCode($row['postalCode'])
                ->setCity($row['city'])
                ->setCountry($country);

            $order->setInvoiceAddress($invoiceAddress);

            $orders[] = $order;
        }

        return $orders;
    }
}
