<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\CommerceBundle\Service\Map\MapBuilder;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;

/**
 * Class MapConfig
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MapConfig
{
    /**
     * @var string
     */
    private $mode;

    /**
     * @var CustomerGroupInterface[]
     */
    private $groups;

    /**
     * @var string
     */
    private $search;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->mode = MapBuilder::MODE_INVOICE;
        $this->groups = new ArrayCollection();
    }

    /**
     * Returns the mode.
     *
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Sets the mode.
     *
     * @param string $mode
     *
     * @return MapConfig
     */
    public function setMode(string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Returns the groups.
     *
     * @return ArrayCollection|CustomerGroupInterface[]
     */
    public function getGroups(): ArrayCollection
    {
        return $this->groups;
    }

    /**
     * Adds the customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return MapConfig
     */
    public function addGroup(CustomerGroupInterface $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }

        return $this;
    }

    /**
     * Adds the customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return MapConfig
     */
    public function removeGroup(CustomerGroupInterface $group): self
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
        }

        return $this;
    }

    /**
     * Returns the search.
     *
     * @return string
     */
    public function getSearch(): ?string
    {
        return $this->search;
    }

    /**
     * Sets the search.
     *
     * @param string $search
     *
     * @return MapConfig
     */
    public function setSearch(string $search): self
    {
        $this->search = $search;

        return $this;
    }
}
