<?php

namespace Ekyna\Bundle\CommerceBundle\Repository;

use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\ProductRepository as BaseRepository;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Util\ResourceRepositoryTrait;

/**
 * Class ProductRepository
 * @package Ekyna\Bundle\CommerceBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductRepository extends BaseRepository implements ResourceRepositoryInterface
{
    use ResourceRepositoryTrait;
}
