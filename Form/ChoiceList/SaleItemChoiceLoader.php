<?php

namespace Ekyna\Bundle\CommerceBundle\Form\ChoiceList;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

/**
 * Class SaleItemChoiceLoader
 * @package Ekyna\Bundle\CommerceBundle\Form\ChoiceList
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemChoiceLoader implements ChoiceLoaderInterface
{
    /**
     * @var SaleInterface
     */
    private $sale;

    /**
     * @var int
     */
    private $depth;

    /**
     * @var bool
     */
    private $public;

    /**
     * @var ArrayChoiceList
     */
    private $choiceList;


    /**
     * Constructor.
     *
     * @param SaleInterface $sale
     * @param bool          $public
     * @param int           $depth
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
    public function loadChoiceList($value = null)
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
    public function loadChoicesForValues(array $values, $value = null)
    {
        return $this->loadChoiceList($value)->getChoicesForValues($values);
    }

    /**
     * @inheritDoc
     */
    public function loadValuesForChoices(array $choices, $value = null)
    {
        return $this->loadChoiceList($value)->getValuesForChoices($choices);
    }

    /**
     * Loads the sale items.
     *
     * @return array
     */
    public function loadItems()
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
    private function loadChildren(array &$list, SaleItemInterface $item, int $depth)
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
