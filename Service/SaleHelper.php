<?php

namespace Ekyna\Bundle\CommerceBundle\Service;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleCouponType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleQuantitiesType;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\Updater\SaleUpdaterInterface;
use Ekyna\Component\Commerce\Common\View\ViewBuilder;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Class SaleHelper
 * @package Ekyna\Bundle\CommerceBundle\Service
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleHelper
{
    /**
     * @var SubjectHelperInterface
     */
    private $subjectHelper;

    /**
     * @var SaleFactoryInterface
     */
    private $saleFactory;

    /**
     * @var SaleUpdaterInterface
     */
    private $saleUpdater;

    /**
     * @var ViewBuilder
     */
    private $viewBuilder;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;


    /**
     * Constructor.
     *
     * @param SubjectHelperInterface $subjectHelper
     * @param SaleFactoryInterface   $saleFactory
     * @param SaleUpdaterInterface   $saleUpdater
     * @param ViewBuilder            $viewBuilder
     * @param FormFactoryInterface   $formFactory
     */
    public function __construct(
        SubjectHelperInterface $subjectHelper,
        SaleFactoryInterface $saleFactory,
        SaleUpdaterInterface $saleUpdater,
        ViewBuilder $viewBuilder,
        FormFactoryInterface $formFactory
    ) {
        $this->subjectHelper = $subjectHelper;
        $this->saleFactory = $saleFactory;
        $this->saleUpdater = $saleUpdater;
        $this->viewBuilder = $viewBuilder;
        $this->formFactory = $formFactory;
    }

    /**
     * Returns the subject helper.
     *
     * @return SubjectHelperInterface
     */
    public function getSubjectHelper()
    {
        return $this->subjectHelper;
    }

    /**
     * Returns the view builder.
     *
     * @return ViewBuilder
     */
    public function getViewBuilder()
    {
        return $this->viewBuilder;
    }

    /**
     * Returns the sale factory.
     *
     * @return SaleFactoryInterface
     */
    public function getSaleFactory()
    {
        return $this->saleFactory;
    }

    /**
     * Returns the form factory.
     *
     * @return FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * Recalculate the whole sale.
     *
     * @param Model\SaleInterface $sale
     *
     * @return bool Whether the sale has been changed or not.
     */
    public function recalculate(Model\SaleInterface $sale)
    {
        return $this->saleUpdater->recalculate($sale);
    }

    /**
     * Builds the sale view.
     *
     * @param Model\SaleInterface $sale
     * @param array               $options
     *
     * @return \Ekyna\Component\Commerce\Common\View\SaleView
     */
    public function buildView(Model\SaleInterface $sale, array $options = [])
    {
        return $this->viewBuilder->buildSaleView($sale, $options);
    }

    /**
     * Creates the items quantities form.
     *
     * @param Model\SaleInterface $sale
     * @param array               $options
     *
     * @return FormInterface
     */
    public function createQuantitiesForm(Model\SaleInterface $sale, array $options = [])
    {
        return $this->formFactory->create(SaleQuantitiesType::class, $sale, $options);
    }

    /**
     * Creates the coupon code form.
     *
     * @param array $options
     *
     * @return FormInterface
     */
    public function createCouponForm(array $options = [])
    {
        return $this->formFactory->create(SaleCouponType::class, null, $options);
    }

    /**
     * Adds the given item to the given sale (or merges with same item).
     *
     * @param Model\SaleInterface     $sale
     * @param Model\SaleItemInterface $item
     *
     * @return Model\SaleItemInterface The resulting item (eventually the 'merged in' one)
     */
    public function addItem(Model\SaleInterface $sale, Model\SaleItemInterface $item)
    {
        $hash = $item->getHash();

        foreach ($sale->getItems() as $i) {
            $ih = $i->getHash();
            if ($hash === $ih) {
                $i->setQuantity($i->getQuantity() + $item->getQuantity());

                return $i;
            }
        }

        $sale->addItem($item);

        return $item;
    }

    /**
     * Finds the item by its id.
     *
     * @param Model\SaleInterface|Model\SaleItemInterface $saleOrItem
     * @param int                                         $itemId
     *
     * @return Model\SaleItemInterface|null
     */
    public function findItemById($saleOrItem, $itemId)
    {
        if ($saleOrItem instanceof Model\SaleInterface) {
            foreach ($saleOrItem->getItems() as $item) {
                if ($itemId == $item->getId()) {
                    return $item;
                }
                if (null !== $result = $this->findItemById($item, $itemId)) {
                    return $result;
                }
            }
        } elseif ($saleOrItem instanceof Model\SaleItemInterface) {
            foreach ($saleOrItem->getChildren() as $item) {
                if ($itemId == $item->getId()) {
                    return $item;
                }
                if (null !== $result = $this->findItemById($item, $itemId)) {
                    return $result;
                }
            }
        } else {
            throw new InvalidArgumentException('Expected sale or sale item.');
        }

        return null;
    }

    /**
     * Finds the sale adjustment by its id.
     *
     * @param Model\SaleInterface $sale
     * @param int                 $adjustmentId
     *
     * @return Model\AdjustmentInterface|null
     */
    public function findAdjustmentById(Model\SaleInterface $sale, $adjustmentId)
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
     *
     * @param Model\SaleInterface $sale
     * @param int                 $attachmentId
     *
     * @return Model\SaleAttachmentInterface|null
     */
    public function findAttachmentById(Model\SaleInterface $sale, $attachmentId)
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
     *
     * @param Model\SaleInterface|Model\SaleItemInterface $saleOrItem
     * @param int                                         $adjustmentId
     *
     * @return Model\AdjustmentInterface|null
     */
    public function findItemAdjustmentById($saleOrItem, $adjustmentId)
    {
        if ($saleOrItem instanceof Model\SaleInterface) {
            foreach ($saleOrItem->getItems() as $item) {
                if (null !== $result = $this->findItemAdjustmentById($item, $adjustmentId)) {
                    return $result;
                }
            }
        } elseif ($saleOrItem instanceof Model\SaleItemInterface) {
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
        } else {
            throw new InvalidArgumentException('Expected sale or sale item.');
        }

        return null;
    }

    /**
     * Removes the item by its id.
     *
     * @param Model\SaleInterface|Model\SaleItemInterface $saleOrItem
     * @param int                                         $itemId
     *
     * @return bool
     */
    public function removeItemById($saleOrItem, $itemId)
    {
        if ($saleOrItem instanceof Model\SaleInterface) {
            foreach ($saleOrItem->getItems() as $item) {
                if ($itemId == $item->getId()) {
                    $saleOrItem->removeItem($item);

                    return true;
                }
                if ((!$item->isImmutable()) && $this->removeItemById($item, $itemId)) {
                    return true;
                }
            }
        } elseif ($saleOrItem instanceof Model\SaleItemInterface) {
            foreach ($saleOrItem->getChildren() as $item) {
                if ($itemId == $item->getId()) {
                    $saleOrItem->removeChild($item);

                    return true;
                }
                if ((!$item->isImmutable()) && $this->removeItemById($item, $itemId)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Removes the sale adjustment by its id.
     *
     * @param Model\SaleInterface $sale
     * @param int                 $adjustmentId
     *
     * @return bool
     */
    public function removeAdjustmentById(Model\SaleInterface $sale, $adjustmentId)
    {
        if (null !== $adjustment = $this->findAdjustmentById($sale, $adjustmentId)) {
            if (!$adjustment->isImmutable()) {
                $sale->removeAdjustment($adjustment);

                return true;
            }
        }

        return false;
    }

    /**
     * Removes the sale attachment by its id.
     *
     * @param Model\SaleInterface $sale
     * @param int                 $attachmentId
     *
     * @return bool
     */
    public function removeAttachmentById(Model\SaleInterface $sale, $attachmentId)
    {
        if (null !== $attachment = $this->findAttachmentById($sale, $attachmentId)) {
            if (!$attachment->isInternal()) {
                $sale->removeAttachment($attachment);

                return true;
            }
        }

        return false;
    }
}
