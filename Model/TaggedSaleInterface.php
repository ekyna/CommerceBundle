<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\CmsBundle\Model as Cms;

/**
 * Interface ItemTagsSubjectInterface
 * @package Ekyna\Bundle\CommerceBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaggedSaleInterface extends Cms\TagsSubjectInterface
{
    public function hasItemsTag(Cms\TagInterface $tag): bool;

    public function addItemsTag(Cms\TagInterface $tag): TaggedSaleInterface;

    public function removeItemsTag(Cms\TagInterface $tag): TaggedSaleInterface;

    /**
     * @return Collection|Cms\TagInterface[]
     */
    public function getItemsTags(): Collection;

    /**
     * @return Collection|Cms\TagInterface[]
     */
    public function getAllTags(): Collection;
}
