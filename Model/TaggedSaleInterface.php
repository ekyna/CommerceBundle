<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Ekyna\Bundle\CmsBundle\Model as Cms;

/**
 * Interface ItemTagsSubjectInterface
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaggedSaleInterface extends Cms\TagsSubjectInterface
{
    /**
     * Returns whether the subject has the given item tag.
     *
     * @param Cms\TagInterface $tag
     *
     * @return bool
     */
    public function hasItemsTag(Cms\TagInterface $tag);

    /**
     * Adds the given item tag.
     *
     * @param Cms\TagInterface $tag
     *
     * @return $this|TaggedSaleInterface
     */
    public function addItemsTag(Cms\TagInterface $tag);

    /**
     * Removes the given item tag.
     *
     * @param Cms\TagInterface $tag
     *
     * @return $this|TaggedSaleInterface
     */
    public function removeItemsTag(Cms\TagInterface $tag);

    /**
     * Returns whether the subject has the given item tag.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|Cms\TagInterface[]
     */
    public function getItemsTags();

    /**
     * Returns all the tags.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|Cms\TagInterface[]
     */
    public function getAllTags();
}