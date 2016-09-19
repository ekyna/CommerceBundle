<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Product;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Product\Model\BundleSlotInterface;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Product\Model\ProductTypes;
use Ekyna\Component\Commerce\Product\Repository\ProductRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Class ProductProvider
 * @package Ekyna\Bundle\CommerceBundle\Service\Product
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductProvider implements SubjectProviderInterface
{
    const NAME = 'product';

    /**
     * @var ProductRepositoryInterface
     */
    private $repository;

    /**
     * @var ItemBuilder
     */
    private $itemBuilder;

    /**
     * @var FormBuilder
     */
    private $formBuilder;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $repository
     * @param ItemBuilder                $itemBuilder
     * @param FormBuilder                $formBuilder
     */
    public function __construct(
        ProductRepositoryInterface $repository,
        ItemBuilder $itemBuilder,
        FormBuilder $formBuilder
    ) {
        $this->repository = $repository;
        $this->itemBuilder = $itemBuilder;
        $this->formBuilder = $formBuilder;
    }

    /**
     * @inheritdoc
     */
    public function needChoice(SaleItemInterface $item)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function buildChoiceForm(FormInterface $form)
    {
        $this->formBuilder->buildChoiceForm($form);
    }

    /**
     * @inheritdoc
     */
    public function handleChoiceSubmit(SaleItemInterface $item)
    {
        $product = $this->getItemProduct($item);

        $data = [
            SubjectProviderInterface::DATA_KEY => $this->getName(),
            'id'                               => $product->getId(),
        ];

        $item->setSubjectData(array_replace((array)$item->getSubjectData(), $data));
    }

    /**
     * @inheritdoc
     */
    public function prepareItem(SaleItemInterface $item)
    {
        if (null === $product = $item->getSubject()) {
            $product = $this->resolve($item);
            $item->setSubject($product); // TODO May be done by resolve()
        }

        if (!$product instanceof ProductInterface) {
            throw new InvalidArgumentException('Unexpected subject.');
        }

        // TODO move to item builder (can't actually because of the resolve() usage)
        // If bundle/configurable product
        if (in_array($product->getType(), [ProductTypes::TYPE_BUNDLE, ProductTypes::TYPE_CONFIGURABLE])) {
            $itemClass = get_class($item);

            // For each bundle/configurable slots
            foreach ($product->getBundleSlots() as $bundleSlot) {
                /** @var \Ekyna\Component\Commerce\Product\Model\BundleChoiceInterface $defaultChoice */
                $defaultChoice = $bundleSlot->getChoices()->first();
                $choiceProducts = [];

                // Valid and default slot product(s)
                foreach ($bundleSlot->getChoices() as $choice) {
                    $choiceProducts[] = $choice->getProduct();
                }

                // Find slot matching item
                if ($item->hasChildren()) {
                    foreach ($item->getChildren() as $child) {
                        // Check bundle slot id
                        $childBundleSlotId = intval($child->getSubjectData(BundleSlotInterface::ITEM_DATA_KEY));
                        if ($childBundleSlotId != $bundleSlot->getId()) {
                            continue;
                        }

                        // Get/resolve item subject
                        if (null === $childProduct = $child->getSubject()) {
                            $childProduct = $this->resolve($child);
                        }

                        // Invalid choice : set default
                        if (!in_array($childProduct, $choiceProducts)) {
                            $child
                                ->setSubject($defaultChoice->getProduct())
                                ->setQuantity($defaultChoice->getMinQuantity());
                        }

                        $child->setPosition($bundleSlot->getPosition());

                        // Next bundle slot
                        continue 2;
                    }
                }

                // Item not found : create it
                /** @var SaleItemInterface $bundleSlotItem */
                $bundleSlotItem = new $itemClass;
                $bundleSlotItem
                    ->setSubject($defaultChoice->getProduct())
                    ->setSubjectData(BundleSlotInterface::ITEM_DATA_KEY, $bundleSlot->getId())
                    ->setQuantity($defaultChoice->getMinQuantity())
                    ->setPosition($bundleSlot->getPosition());

                $item->addChild($bundleSlotItem);
            }

            // TODO Sort items by position ?
        }
    }

    /**
     * @inheritdoc
     */
    public function buildItemForm(FormInterface $form, SaleItemInterface $item)
    {
        $this->formBuilder->buildItemForm($form, $item);
    }

    /**
     * @inheritdoc
     */
    public function handleItemSubmit(SaleItemInterface $item)
    {
        $product = $this->getItemProduct($item);

        $this->itemBuilder->buildItem($item, $product);
    }

    /**
     * @inheritdoc
     */
    public function resolve(SaleItemInterface $item)
    {
        $this->assertSupportsItem($item);

        $data = $item->getSubjectData();
        if (!array_key_exists('id', $data)) {
            throw new InvalidArgumentException("Unexpected item subject data.");
        }
        $dataId = intval($data['id']);

        if (
            (null !== $product = $item->getSubject())
            && ($product instanceof ProductInterface)
            && ($product->getId() !== $dataId)
        ) {
            return $product;
        }

        if ((0 < $dataId) && (null !== $product = $this->repository->findOneById($data['id']))) {
            $item->setSubject($product);
        } else {
            // TODO $item->setSubject(null);
            // TODO return null;
            throw new InvalidArgumentException("Failed to resolve item subject.");
        }

        return $product;
    }

    /**
     * @inheritdoc
     */
    public function supportsSubject($subject)
    {
        return $subject instanceof ProductInterface;
    }

    /**
     * @inheritdoc
     */
    public function supportsItem(SaleItemInterface $item)
    {
        return $item->getSubjectData(SubjectProviderInterface::DATA_KEY) === self::NAME;
    }

    /**
     * Asserts that the sale item is supported.
     *
     * @param SaleItemInterface $item
     *
     * @throws InvalidArgumentException
     */
    protected function assertSupportsItem(SaleItemInterface $item)
    {
        if (!$this->supportsItem($item)) {
            throw new InvalidArgumentException('Unsupported sale item.');
        }
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return 'ekyna_commerce.product.label.singular';
    }

    /**
     * Asserts that the item subject is set.
     *
     * @param SaleItemInterface $item
     *
     * @return ProductInterface
     * @throws RuntimeException
     */
    private function getItemProduct(SaleItemInterface $item)
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        if (null === $subject = $item->getSubject()) {
            throw new RuntimeException('Item subject must be set.');
        }

        if (!$subject instanceof ProductInterface) {
            throw new InvalidArgumentException("Expected instance of " . ProductInterface::class);
        }

        return $subject;
    }
}
