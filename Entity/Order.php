<?php

namespace Ekyna\Bundle\CommerceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\CmsBundle\Model as Cms;
use Ekyna\Bundle\CommerceBundle\Model;
use Ekyna\Component\Commerce\Order\Entity\Order as BaseOrder;

/**
 * Class Order
 * @package Ekyna\Bundle\CommerceBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Order extends BaseOrder implements Model\OrderInterface
{
    use Model\InChargeSubjectTrait,
        Cms\TagsSubjectTrait;

    /**
     * @var ArrayCollection|Cms\TagInterface[]
     */
    protected $itemsTags;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->tags = new ArrayCollection();
        $this->itemsTags = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function hasItemsTag(Cms\TagInterface $tag)
    {
        return $this->itemsTags->contains($tag);
    }

    /**
     * @inheritdoc
     */
    public function addItemsTag(Cms\TagInterface $tag)
    {
        if (!$this->itemsTags->contains($tag)) {
            $this->itemsTags->add($tag);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeItemsTag(Cms\TagInterface $tag)
    {
        if ($this->itemsTags->contains($tag)) {
            $this->itemsTags->removeElement($tag);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getItemsTags()
    {
        return $this->itemsTags;
    }

    /**
     * @inheritdoc
     */
    public function getAllTags()
    {
        return array_unique(array_merge($this->tags->getValues(), $this->itemsTags->getValues()));
    }
}
