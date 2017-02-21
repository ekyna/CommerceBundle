<?php

namespace Ekyna\Bundle\CommerceBundle\Service;

use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleQuantitiesType;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\Updater\SaleUpdaterInterface;
use Ekyna\Component\Commerce\Common\View\ViewBuilder;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Symfony\Component\Form\Extension\Core\Type;
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
     * Returns the view builder.
     *
     * @return ViewBuilder
     * @deprecated
     */
    public function getViewBuilder()
    {
        return $this->viewBuilder;
    }

    /**
     * Returns the sale factory.
     *
     * @return SaleFactoryInterface
     * @deprecated
     */
    public function getSaleFactory()
    {
        return $this->saleFactory;
    }

    /**
     * Returns the form factory.
     *
     * @return FormFactoryInterface
     * @deprecated
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
        return $this->saleUpdater->recalculate($sale, true);
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
        return $this
            ->formFactory
            ->create(SaleQuantitiesType::class, $sale, $options)
            ->add('submit', Type\SubmitType::class, [
                'label' => 'ekyna_commerce.sale.button.recalculate',
            ]);
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
    public function findSaleAdjustmentById(Model\SaleInterface $sale, $adjustmentId)
    {
        foreach ($sale->getAdjustments() as $adjustment) {
            if ($adjustmentId == $adjustment->getId()) {
                return $adjustment;
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
    public function findSaleItemAdjustmentById($saleOrItem, $adjustmentId)
    {
        if ($saleOrItem instanceof Model\SaleInterface) {
            foreach ($saleOrItem->getItems() as $item) {
                if (null !== $result = $this->findSaleItemAdjustmentById($item, $adjustmentId)) {
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
                if (null !== $result = $this->findSaleItemAdjustmentById($item, $adjustmentId)) {
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
     * @todo remove as no longer used
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
     * @todo remove as no longer used
     */
    public function removeSaleAdjustmentById(Model\SaleInterface $sale, $adjustmentId)
    {
        if (null !== $adjustment = $this->findSaleAdjustmentById($sale, $adjustmentId)) {
            if (!$adjustment->isImmutable()) {
                $sale->removeAdjustment($adjustment);

                return true;
            }
        }

        return false;
    }
}
