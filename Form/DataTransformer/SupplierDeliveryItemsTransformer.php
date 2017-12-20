<?php

namespace Ekyna\Bundle\CommerceBundle\Form\DataTransformer;

use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryInterface;
use Ekyna\Component\Commerce\Supplier\Util\SupplierUtil;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
use Symfony\Component\Form\DataTransformerInterface;
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
     * @var SubjectHelperInterface
     */
    private $subjectHelper;


    /**
     * Constructor.
     *
     * @param ResourceRepositoryInterface $deliveryItemRepository
     * @param SubjectHelperInterface      $subjectHelper
     */
    public function __construct(
        ResourceRepositoryInterface $deliveryItemRepository,
        SubjectHelperInterface $subjectHelper
    ) {
        $this->deliveryItemRepository = $deliveryItemRepository;
        $this->subjectHelper = $subjectHelper;
    }

    /**
     * @inheritdoc
     */
    public function transform($delivery)
    {
        if (!$delivery instanceof SupplierDeliveryInterface) {
            throw new TransformationFailedException("Expected instance of " . SupplierDeliveryInterface::class);
        }

        if (null === $order = $delivery->getOrder()) {
            throw new TransformationFailedException("Supplier delivery's order must be set.");
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
            $remainingQuantity = SupplierUtil::calculateDeliveryRemainingQuantity($orderItem);

            // Create a new delivery item if remaining quantity is greater than zero
            if (0 < $remainingQuantity) {
                $geocode = null;
                $subject = $this->subjectHelper->resolve($orderItem);
                if ($subject instanceof StockSubjectInterface) {
                    $geocode = $subject->getGeocode();
                }

                /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryItemInterface $deliveryItem */
                $deliveryItem = $this->deliveryItemRepository->createNew();
                $deliveryItem
                    ->setOrderItem($orderItem)
                    ->setQuantity($remainingQuantity)
                    ->setGeocode($geocode);

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
            throw new TransformationFailedException("Expected instance of " . SupplierDeliveryInterface::class);
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
