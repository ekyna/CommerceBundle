<?php

namespace Ekyna\Bundle\CommerceBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;

/**
 * Class SupplierCarrierContext
 * @package Ekyna\Bundle\CommerceBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierCarrierContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given The following supplier carriers:
     *
     * @param TableNode $table
     */
    public function createCarriers(TableNode $table)
    {
        $carriers = $this->castCarriersTable($table);

        $manager = $this->getContainer()->get('ekyna_commerce.supplier_carrier.manager');

        foreach ($carriers as $carrier) {
            $manager->persist($carrier);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castCarriersTable(TableNode $table)
    {
        $carrierRepository = $this->getContainer()->get('ekyna_commerce.supplier_carrier.repository');

        $carriers = [];
        foreach ($table as $row) {
            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierCarrierInterface $carrier */
            $carrier = $carrierRepository->createNew();
            $carrier->setName($row['name']);

            $carriers[] = $carrier;
        }

        return $carriers;
    }
}
