<?php

namespace Ekyna\Bundle\CommerceBundle\DataFixtures;

use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Fidry\AliceDataFixtures\ProcessorInterface;

/**
 * Class CommerceProcessor
 * @package Ekyna\Bundle\CommerceBundle\DataFixtures
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CommerceProcessor implements ProcessorInterface
{
    private $referenceSuffix = 100;

    /**
     * @inheritDoc
     */
    public function preProcess(string $id, $object): void
    {
        if ($object instanceof SupplierProductInterface) {
            $this->preProcessSupplierProduct($object);
        }
    }

    private function preProcessSupplierProduct(SupplierProductInterface $product): void
    {
        /** @var \Ekyna\Component\Commerce\Subject\Model\SubjectInterface $subject */
        $subject = $product->getSubjectIdentity()->getSubject();

        $this->referenceSuffix++;

        if (is_a($subject, 'Ekyna\Bundle\ProductBundle\Model\ProductInterface')) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $subject */
            $product->setDesignation($subject->getFullDesignation(true));
        } else {
            $product->setDesignation($subject->getDesignation());
        }

        $product
            ->setReference($subject->getReference() . '-' . $this->referenceSuffix)
            ->setNetPrice(round($subject->getNetPrice() * rand(2,6) / 10 , 2))
            ->setWeight($subject->getWeight());

        switch ($rand = rand(0, 10)) {
            case $rand > 5:
                // Available
                $product->setAvailableStock(rand(50, 150));
                break;
            case $rand > 2:
                // Pre order
                $product
                    ->setAvailableStock(rand(50, 150))
                    ->setEstimatedDateOfArrival(new \DateTime(sprintf('+ %d days', rand(10, 30))));
                break;
            default:
                // Out of stock
                break;
        }
    }

    /**
     * @inheritDoc
     */
    public function postProcess(string $id, $object): void
    {
        return;
    }
}
