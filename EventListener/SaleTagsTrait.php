<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\CmsBundle\Model\TagsSubjectInterface;
use Ekyna\Bundle\CommerceBundle\Model\TaggedSaleInterface;
use Ekyna\Bundle\CommerceBundle\Service\Subject\SubjectHelperInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Trait TagTrait
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
trait SaleTagsTrait
{
    protected SubjectHelperInterface $subjectHelper;

    public function setSubjectHelper(SubjectHelperInterface $subjectHelper): void
    {
        $this->subjectHelper = $subjectHelper;
    }

    protected function updateSaleItemsTags(TaggedSaleInterface $sale): void
    {
        $expected = new ArrayCollection();
        foreach ($sale->getItems() as $item) {
            $this->mergeItemTags($item, $expected);
        }

        $current = $sale->getItemsTags();

        // Remove unexpected tags
        foreach ($current as $tag) {
            if (!$expected->contains($tag)) {
                $current->removeElement($tag);
            }
        }

        // Add new tags
        foreach ($expected as $tag) {
            if (!$current->contains($tag)) {
                $current->add($tag);
            }
        }
    }

    /**
     * Resolves the item tags.
     */
    private function mergeItemTags(SaleItemInterface $item, Collection $tags): void
    {
        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $this->mergeItemTags($child, $tags);
            }
        }

        if (null === $subject = $this->subjectHelper->resolve($item, false)) {
            return;
        }

        if (!$subject instanceof TagsSubjectInterface) {
            return;
        }

        foreach ($subject->getTags() as $tag) {
            if (!$tags->contains($tag)) {
                $tags->add($tag);
            }
        }
    }
}
