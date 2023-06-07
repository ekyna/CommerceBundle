<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\CmsBundle\Model\TagInterface;
use Ekyna\Bundle\CmsBundle\Model\TagsSubjectTrait;

/**
 * Trait TaggedSaleTrait
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @property CustomerInterface $customer
 */
trait TaggedSaleTrait
{
    use TagsSubjectTrait {
        initializeTags as cmsInitializeTags;
    }

    /** @var Collection|TagInterface[] */
    protected Collection $itemsTags;


    protected function initializeTags(): void
    {
        $this->cmsInitializeTags();

        $this->itemsTags = new ArrayCollection();
    }

    public function hasItemsTag(TagInterface $tag): bool
    {
        return $this->itemsTags->contains($tag);
    }

    /**
     * @return $this|TaggedSaleInterface
     */
    public function addItemsTag(TagInterface $tag): TaggedSaleInterface
    {
        if (!$this->itemsTags->contains($tag)) {
            $this->itemsTags->add($tag);
        }

        return $this;
    }

    /**
     * @return $this|TaggedSaleInterface
     */
    public function removeItemsTag(TagInterface $tag): TaggedSaleInterface
    {
        if ($this->itemsTags->contains($tag)) {
            $this->itemsTags->removeElement($tag);
        }

        return $this;
    }

    public function getItemsTags(): Collection
    {
        return $this->itemsTags;
    }

    public function getAllTags(): Collection
    {
        $tags = new ArrayCollection($this->tags->getValues());

        foreach ($this->itemsTags as $tag) {
            if ($tags->contains($tag)) {
                continue;
            }

            $tags->add($tag);
        }

        if (!$this->customer || $this->customer->getTags()->isEmpty()) {
            return $tags;
        }

        foreach ($this->customer->getTags() as $tag) {
            if ($tags->contains($tag)) {
                continue;
            }

            $tags->add($tag);
        }


        return $tags;
    }
}
