<?php

namespace Ekyna\Bundle\CommerceBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class ShipmentZoneContext
 * @package Ekyna\Bundle\CommerceBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentZoneContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following shipment zones:
     *
     * @param TableNode $table
     */
    public function createShipmentZones(TableNode $table)
    {
        $shipmentZones = $this->castShipmentZonesTable($table);

        $manager = $this->getContainer()->get('ekyna_commerce.shipment_zone.manager');

        foreach ($shipmentZones as $shipmentZone) {
            $manager->persist($shipmentZone);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castShipmentZonesTable(TableNode $table)
    {
        $countryRepository = $this->getContainer()->get('ekyna_commerce.country.repository');
        $shipmentZoneRepository = $this->getContainer()->get('ekyna_commerce.shipment_zone.repository');

        $shipmentZones = [];
        foreach ($table as $row) {
            /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentZoneInterface $shipmentZone */
            $shipmentZone = $shipmentZoneRepository->createNew();

            if (isset($row['countries'])) {
                foreach (explode(',', $row['countries']) as $code) {
                    if (null === $country = $countryRepository->findOneByCode($code)) {
                        throw new \InvalidArgumentException("Failed to find the country with code '{$code}'.");
                    }
                    $shipmentZone->addCountry($country);
                }
            } else {
                $shipmentZone->addCountry($countryRepository->findDefault());
            }

            $shipmentZone->setName($row['name']);

            $shipmentZones[] = $shipmentZone;
        }

        return $shipmentZones;
    }
}
