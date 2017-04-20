<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\DataTransformer;

use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryItemInterface;
use Ekyna\Component\Commerce\Supplier\Util\SupplierUtil;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class SupplierDeliveryItemsTransformer
 * @package Ekyna\Bundle\CommerceBundle\Form\DataTransformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryItemsTransformer implements DataTransformerInterface
{
    private ResourceFactoryInterface $deliveryItemFactory;
    private SubjectHelperInterface   $subjectHelper;

    public function __construct(
        ResourceFactoryInterface $deliveryItemFactory,
        SubjectHelperInterface $subjectHelper
    ) {
        $this->deliveryItemFactory = $deliveryItemFactory;
        $this->subjectHelper = $subjectHelper;
    }

    /**
     * @inheritDoc
     */
    public function transform($value)
    {
        if (!$value instanceof SupplierDeliveryInterface) {
            throw new TransformationFailedException('Expected instance of ' . SupplierDeliveryInterface::class);
        }

        if (null === $order = $value->getOrder()) {
            throw new TransformationFailedException('Supplier delivery\'s order must be set.');
        }

        $create = null === $value->getId();

        // For each order items
        foreach ($order->getItems() as $orderItem) {
            // If not deliveryItem not exists
            foreach ($value->getItems() as $deliveryItem) {
                if ($orderItem === $deliveryItem->getOrderItem()) {
                    continue 2;
                }
            }

            // Get the remaining delivery quantity
            $remainingQuantity = SupplierUtil::calculateDeliveryRemainingQuantity($orderItem);

            // Create a new delivery item if remaining quantity is greater than zero
            if (0 < $remainingQuantity) {
                $geocode = null;
                $subject = $this->subjectHelper->resolve($orderItem);
                if ($subject instanceof StockSubjectInterface) {
                    $geocode = $subject->getGeocode();
                }

                /** @var SupplierDeliveryItemInterface $deliveryItem */
                $deliveryItem = $this->deliveryItemFactory->create();
                $deliveryItem->setOrderItem($orderItem);

                if ($create) {
                    $deliveryItem
                        ->setQuantity($remainingQuantity)
                        ->setGeocode($geocode);
                }

                $value->addItem($deliveryItem);
            }
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value)
    {
        if (!$value instanceof SupplierDeliveryInterface) {
            throw new TransformationFailedException('Expected instance of ' . SupplierDeliveryInterface::class);
        }

        // Removed zero quantity delivery item
        foreach ($value->getItems() as $deliveryItem) {
            if (0 >= $deliveryItem->getQuantity()) {
                $value->removeItem($deliveryItem);
            }
        }

        return $value;
    }
}
