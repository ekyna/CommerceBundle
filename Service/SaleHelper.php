<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleCouponType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleQuantitiesType;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface as Adjustment;
use Ekyna\Component\Commerce\Common\Model\SaleAdjustmentInterface as SaleAdjustment;
use Ekyna\Component\Commerce\Common\Model\SaleAttachmentInterface as SaleAttachment;
use Ekyna\Component\Commerce\Common\Model\SaleInterface as Sale;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface as SaleItem;
use Ekyna\Component\Commerce\Common\Updater\SaleUpdaterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Class SaleHelper
 * @package Ekyna\Bundle\CommerceBundle\Service
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleHelper
{
    public function __construct(
        private readonly FactoryHelperInterface $factoryHelper,
        private readonly SaleUpdaterInterface   $saleUpdater,
        private readonly FormFactoryInterface   $formFactory
    ) {
    }

    /**
     * @deprecated
     */
    public function getFactoryHelper(): FactoryHelperInterface
    {
        return $this->factoryHelper;
    }

    /**
     * @deprecated
     */
    public function getFormFactory(): FormFactoryInterface
    {
        return $this->formFactory;
    }

    /**
     * Recalculate the sale.
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function recalculate(Sale $sale): bool
    {
        return $this->saleUpdater->recalculate($sale);
    }

    /**
     * Creates the items quantities form.
     *
     * @deprecated Use SaleViewHelper
     */
    public function createQuantitiesForm(Sale $sale, array $options = []): FormInterface
    {
        return $this->formFactory->create(SaleQuantitiesType::class, $sale, $options);
    }

    /**
     * Creates the coupon code form.
     *
     * @deprecated Use CouponHelper
     */
    public function createCouponForm(array $options = []): FormInterface
    {
        return $this->formFactory->create(SaleCouponType::class, null, $options);
    }

    /**
     * Adds the given item to the given sale (or merges with same item).
     *
     * @return SaleItem The resulting item (possibly the 'merged in' item)
     */
    public function addItem(Sale $sale, SaleItem $item): SaleItem
    {
        $hash = $item->getHash();

        foreach ($sale->getItems() as $i) {
            if ($hash === $i->getHash()) {
                $i->setQuantity($i->getQuantity() + $item->getQuantity());

                return $i;
            }
        }

        $sale->addItem($item);

        return $item;
    }

    /**
     * Finds the item by its id.
     */
    public function findItemById(Sale|SaleItem $saleOrItem, int $itemId, bool $rootOnly = false): ?SaleItem
    {
        if ($saleOrItem instanceof Sale) {
            $list = $saleOrItem->getItems();
        } elseif ($rootOnly) {
            return null;
        } else {
            $list = $saleOrItem->getChildren();
        }

        foreach ($list as $item) {
            if ($itemId == $item->getId()) {
                return $item;
            }

            if ($rootOnly) {
                continue;
            }

            if (null !== $child = $this->findItemById($item, $itemId)) {
                return $child;
            }
        }

        return null;
    }

    /**
     * Finds the sale adjustment by its id.
     */
    public function findAdjustmentById(Sale $sale, int $adjustmentId): ?SaleAdjustment
    {
        foreach ($sale->getAdjustments() as $adjustment) {
            if ($adjustmentId == $adjustment->getId()) {
                return $adjustment;
            }
        }

        return null;
    }

    /**
     * Finds the sale attachment by its id.
     */
    public function findAttachmentById(Sale $sale, int $attachmentId): ?SaleAttachment
    {
        foreach ($sale->getAttachments() as $attachment) {
            if ($attachmentId == $attachment->getId()) {
                return $attachment;
            }
        }

        return null;
    }

    /**
     * Finds the sale item adjustment by its id.
     */
    public function findItemAdjustmentById(Sale|SaleItem $saleOrItem, int $adjustmentId): ?Adjustment
    {
        if ($saleOrItem instanceof Sale) {
            foreach ($saleOrItem->getItems() as $item) {
                if (null !== $result = $this->findItemAdjustmentById($item, $adjustmentId)) {
                    return $result;
                }
            }

            return null;
        }

        foreach ($saleOrItem->getAdjustments() as $adjustment) {
            if ($adjustmentId == $adjustment->getId()) {
                return $adjustment;
            }
        }

        foreach ($saleOrItem->getChildren() as $item) {
            if (null !== $result = $this->findItemAdjustmentById($item, $adjustmentId)) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Removes the item by its id.
     */
    public function removeItemById(Sale|SaleItem $saleOrItem, int $itemId): bool
    {
        $list = $saleOrItem instanceof Sale
            ? $saleOrItem->getItems()
            : $saleOrItem->getChildren();

        foreach ($list as $item) {
            if ($itemId !== $item->getId()) {
                if ($this->removeItemById($item, $itemId)) {
                    return true;
                }

                continue;
            }

            if ($item->isImmutable()) {
                return false;
            }

            // TODO Prevent if invoiced or shipped

            if ($saleOrItem instanceof Sale) {
                $saleOrItem->removeItem($item);
            } else {
                $saleOrItem->removeChild($item);
            }

            return true;
        }

        return false;
    }

    /**
     * Removes the sale adjustment by its id.
     */
    public function removeAdjustmentById(Sale $sale, int $adjustmentId): bool
    {
        if (null === $adjustment = $this->findAdjustmentById($sale, $adjustmentId)) {
            return false;
        }

        if ($adjustment->isImmutable()) {
            return false;
        }

        $sale->removeAdjustment($adjustment);

        return true;
    }

    /**
     * Removes the sale attachment by its id.
     */
    public function removeAttachmentById(Sale $sale, int $attachmentId): bool
    {
        if (null === $attachment = $this->findAttachmentById($sale, $attachmentId)) {
            return false;
        }

        if ($attachment->isInternal()) {
            return false;
        }

        $sale->removeAttachment($attachment);

        return true;
    }
}
