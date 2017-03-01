<?php

namespace Ekyna\Bundle\CommerceBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class ShipmentPriceContext
 * @package Ekyna\Bundle\CommerceBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPriceContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following shipment prices:
     *
     * @param TableNode $table
     */
    public function createShipmentPrices(TableNode $table)
    {
        $shipmentPrices = $this->castShipmentPricesTable($table);

        $manager = $this->getContainer()->get('ekyna_commerce.shipment_price.manager');

        foreach ($shipmentPrices as $shipmentPrice) {
            $manager->persist($shipmentPrice);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castShipmentPricesTable(TableNode $table)
    {
        $shipmentMethodRepository = $this->getContainer()->get('ekyna_commerce.shipment_method.repository');
        $shipmentZoneRepository = $this->getContainer()->get('ekyna_commerce.shipment_zone.repository');
        $shipmentPriceRepository = $this->getContainer()->get('ekyna_commerce.shipment_price.repository');

        $shipmentPrices = [];
        foreach ($table as $row) {
            if (null === $shipmentMethod = $shipmentMethodRepository->findOneBy(['name' => $row['method']])) {
                throw new \InvalidArgumentException("Failed to find the shipment method with name '{$row['method']}'.");
            }
            if (null === $shipmentZone = $shipmentZoneRepository->findOneBy(['name' => $row['zone']])) {
                throw new \InvalidArgumentException("Failed to find the shipment zone with name '{$row['zone']}'.");
            }

            /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface $shipmentPrice */
            $shipmentPrice = $shipmentPriceRepository->createNew();
            $shipmentPrice
                ->setMethod($shipmentMethod)
                ->setZone($shipmentZone)
                ->setWeight($row['weight'])
                ->setNetPrice($row['price']);

            $shipmentPrices[] = $shipmentPrice;
        }

        return $shipmentPrices;
    }
}
