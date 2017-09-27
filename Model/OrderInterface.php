<?php

namespace Ekyna\Bundle\CommerceBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\CmsBundle\Model as Cms;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface as BaseInterface;

/**
 * Interface OrderInterface
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderInterface extends BaseInterface, Cms\TagsSubjectInterface
{
    /**
     * Returns the 'in charge' user.
     *
     * @return UserInterface
     */
    public function getInCharge();

    /**
     * Sets the 'in charge' user.
     *
     * @param UserInterface $user
     *
     * @return $this|OrderInterface
     */
    public function setInCharge(UserInterface $user = null);

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
