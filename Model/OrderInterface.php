<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\CmsBundle\Model as Cms;
use Ekyna\Component\Commerce\Order\Model\OrderInterface as BaseInterface;

/**
 * Interface OrderInterface
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderInterface extends BaseInterface, InChargeSubjectInterface, Cms\TagsSubjectInterface
{
    /**
     * Returns whether the sale has the given items tag.
     *
     * @param Cms\TagInterface $tag
     *
     * @return bool
     */
    public function hasItemsTag(Cms\TagInterface $tag);

    /**
     * Adds the items tag.
     *
     * @param Cms\TagInterface $tag
     *
     * @return $this|OrderInterface
     */
    public function addItemsTag(Cms\TagInterface $tag);

    /**
     * Removes the items tag.
     *
     * @param Cms\TagInterface $tag
     *
     * @return $this|OrderInterface
     */
    public function removeItemsTag(Cms\TagInterface $tag);

    /**
     * Returns the items tags.
     *
     * @return ArrayCollection|Cms\TagInterface[]
     */
    public function getItemsTags();

    /**
     * Returns all the tags.
     *
     * @return ArrayCollection|Cms\TagInterface[]
     */
    public function getAllTags();
}
