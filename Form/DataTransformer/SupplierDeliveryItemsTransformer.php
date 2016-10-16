<?php

namespace Ekyna\Bundle\CommerceBundle\Form\DataTransformer;

use Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class SupplierDeliveryItemsTransformer
 * @package Ekyna\Bundle\CommerceBundle\Form\DataTransformer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryItemsTransformer implements DataTransformerInterface
{
    /**
     * @var ResourceRepositoryInterface
     */
    private $deliveryItemRepository;


    /**
     * Constructor.
     *
     * @param ResourceRepositoryInterface $deliveryItemRepository
     */
    public function __construct(ResourceRepositoryInterface $deliveryItemRepository)
    {
        $this->deliveryItemRepository = $deliveryItemRepository;
    }

    /**
     * @inheritdoc
     */
    public function transform($delivery)
    {
        if (!$delivery instanceof SupplierDeliveryInterface) {
            throw new InvalidArgumentException("Expected instance of SupplierDeliveryInterface.");
        }

        if (null === $order = $delivery->getOrder()) {
            throw new RuntimeException("Supplier delivery's order must be set.");
        }

        // For each order items
        foreach ($order->getItems() as $orderItem) {
            // If not deliveryItem not exists
            foreach ($delivery->getItems() as $deliveryItem) {
                if ($orderItem === $deliveryItem->getOrderItem()) {
                    continue 2;
                }
            }

            // Get the remaining delivery quantity
            $remainingQuantity = $orderItem->getDeliveryRemainingQuantity($delivery);

            // Create a new delivery item if remaining quantity is greater than zero
            if (0 < $remainingQuantity) {
                /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryItemInterface $deliveryItem */
                $deliveryItem = $this->deliveryItemRepository->createNew();
                $deliveryItem
                    ->setOrderItem($orderItem)
                    ->setQuantity($remainingQuantity);

                $delivery->addItem($deliveryItem);
            }
        }

        return $delivery;
    }

    /**
     * @inheritdoc
     */
    public function reverseTransform($delivery)
    {
        if (!$delivery instanceof SupplierDeliveryInterface) {
            throw new InvalidArgumentException("Expected instance of SupplierDeliveryInterface.");
        }

        // Removed zero quantity delivery item
        foreach ($delivery->getItems() as $deliveryItem) {
            if (0 >= $deliveryItem->getQuantity()) {
                $delivery->removeItem($deliveryItem);
            }
        }

        return $delivery;
    }
}
