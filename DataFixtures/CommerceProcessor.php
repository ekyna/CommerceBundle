<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\DataFixtures;

use DateTime;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierProductInterface;
use Fidry\AliceDataFixtures\ProcessorInterface;

/**
 * Class CommerceProcessor
 * @package Ekyna\Bundle\CommerceBundle\DataFixtures
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CommerceProcessor implements ProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function preProcess(string $id, $object): void
    {
        if ($object instanceof SupplierProductInterface) {
            $this->preProcessSupplierProduct($object);
        } elseif ($object instanceof SupplierOrderItemInterface) {
            $this->preProcessSupplierOrderItem($object);
        }
    }

    private function preProcessSupplierOrderItem(SupplierOrderItemInterface $item): void
    {
        $subject = $item->getProduct()->getSubjectIdentity()->getSubject();

        if (!$subject instanceof StockSubjectInterface) {
            throw new UnexpectedTypeException($subject, StockSubjectInterface::class);
        }

        $item
            ->setDesignation((string)$subject)
            ->setReference($subject->getReference() . '-SUPP')
            ->setNetPrice($subject->getNetPrice()->div(2))
            ->setWeight(clone $subject->getWeight())
            ->setTaxGroup($subject->getTaxGroup());
    }

    private function preProcessSupplierProduct(SupplierProductInterface $product): void
    {
        $subject = $product->getSubjectIdentity()->getSubject();

        if (!$subject instanceof StockSubjectInterface) {
            throw new UnexpectedTypeException($subject, StockSubjectInterface::class);
        }

        $product
            ->setDesignation((string)$subject)
            ->setReference($subject->getReference() . '-SUPP')
            ->setNetPrice($subject->getNetPrice()->div(2))
            ->setWeight(clone $subject->getWeight())
            ->setTaxGroup($subject->getTaxGroup());

        switch ($rand = rand(0, 10)) {
            case $rand > 5:
                // Available
                $product->setAvailableStock(new Decimal(rand(50, 150)));
                break;
            case $rand > 2:
                // Pre order
                $product
                    ->setAvailableStock(new Decimal(rand(50, 150)))
                    ->setEstimatedDateOfArrival(new DateTime(sprintf('+ %d days', rand(10, 30))));
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
    }
}
