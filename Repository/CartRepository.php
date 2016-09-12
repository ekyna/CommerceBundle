<?php

namespace Ekyna\Bundle\CommerceBundle\Repository;

use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\CartRepository as BaseRepository;

/**
 * Class CartRepository
 * @package Ekyna\Bundle\CommerceBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartRepository extends BaseRepository
{
    /**
     * @inheritdoc
     */
    public function findOneById($id)
    {
        return $this->find($id);
    }
}
