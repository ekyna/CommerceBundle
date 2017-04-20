<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\ChoiceList;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

/**
 * Class SaleItemChoiceLoader
 * @package Ekyna\Bundle\CommerceBundle\Form\ChoiceList
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemChoiceLoader implements ChoiceLoaderInterface
{
    private SaleInterface $sale;
    private ?int $depth;
    private bool $public;

    private ?ArrayChoiceList $choiceList = null;


    /**
     * Constructor.
     *
     * @param SaleInterface $sale
     * @param bool          $public
     * @param int|null      $depth
     */
    public function __construct(SaleInterface $sale, bool $public = true, int $depth = null)
    {
        $this->sale = $sale;
        $this->public = $public;
        $this->depth = $depth;
    }

    /**
     * @inheritDoc
     */
    public function loadChoiceList(callable $value = null): ChoiceListInterface
    {
        if ($this->choiceList) {
            return $this->choiceList;
        }

        $items = $this->loadItems();

        return $this->choiceList = new ArrayChoiceList($items, $value);
    }

    /**
     * @inheritDoc
     */
    public function loadChoicesForValues(array $values, callable $value = null): array
    {
        return $this->loadChoiceList($value)->getChoicesForValues($values);
    }

    /**
     * @inheritDoc
     */
    public function loadValuesForChoices(array $choices, callable $value = null): array
    {
        return $this->loadChoiceList($value)->getValuesForChoices($choices);
    }

    /**
     * Loads the sale items.
     *
     * @return array
     */
    public function loadItems(): array
    {
        $items = [];

        foreach ($this->sale->getItems() as $item) {
            $items[] = $item;

            $this->loadChildren($items, $item, 1);
        }

        return $items;
    }

    /**
     * Loads the item's children.
     *
     * @param array             $list
     * @param SaleItemInterface $item
     * @param int               $depth
     */
    private function loadChildren(array &$list, SaleItemInterface $item, int $depth): void
    {
        foreach ($item->getChildren() as $child) {
            if ($this->public && $child->isPrivate()) {
                continue;
            }

            $list[] = $child;

            if (0 < $this->depth && $this->depth < $depth) {
                continue;
            }

            $this->loadChildren($list, $child, $depth + 1);
        }
    }
}
