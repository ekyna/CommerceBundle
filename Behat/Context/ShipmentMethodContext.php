<?php

namespace Ekyna\Bundle\CommerceBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class ShipmentMethodContext
 * @package Ekyna\Bundle\CommerceBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following shipment methods:
     *
     * @param TableNode $table
     */
    public function createShipmentMethods(TableNode $table)
    {
        $shipmentMethods = $this->castShipmentMethodsTable($table);

        $manager = $this->getContainer()->get('ekyna_commerce.shipment_method.manager');

        foreach ($shipmentMethods as $shipmentMethod) {
            $manager->persist($shipmentMethod);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castShipmentMethodsTable(TableNode $table)
    {
        $taxGroupRepository = $this->getContainer()->get('ekyna_commerce.tax_group.repository');
        $shipmentMethodRepository = $this->getContainer()->get('ekyna_commerce.shipment_method.repository');

        $shipmentMethods = [];
        foreach ($table as $row) {
            /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface $shipmentMethod */
            $shipmentMethod = $shipmentMethodRepository->createNew();

            if (isset($row['taxGroup'])) {
                if (null === $taxGroup = $taxGroupRepository->findOneBy(['name' => $row['taxGroup']])) {
                    throw new \InvalidArgumentException("Failed to find the tax group with name '{$row['taxGroup']}'.");
                }
                $shipmentMethod->setTaxGroup($taxGroup);
            } else {
                $shipmentMethod->setTaxGroup($taxGroupRepository->findDefault());
            }

            # Gateway
            $platform = isset($row['platform']) ? $row['platform'] : 'noop';
            $config = isset($row['config']) ? $row['config'] : null;

            $shipmentMethod
                ->setName($row['name'])
                ->setPlatformName($platform)
                ->setGatewayConfig($config)
                ->setAvailable(isset($row['available']) ? $row['available'] : true)
                ->setEnabled(isset($row['enabled']) ? $row['enabled'] : true)
                ->translate('fr', true)
                ->setTitle(isset($row['title']) ? $row['title'] :  'Titre '.$row['name'])
                ->setDescription(isset($row['description']) ? $row['description'] :  '<p>Description '.$row['name'].'</p>');

            $shipmentMethods[] = $shipmentMethod;
        }

        return $shipmentMethods;
    }
}
