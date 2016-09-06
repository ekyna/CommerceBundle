<?php

namespace Ekyna\Bundle\CommerceBundle\Repository;

use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\TaxRuleRepository as BaseRepository;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Util\ResourceRepositoryTrait;

/**
 * Class TaxRuleRepository
 * @package Ekyna\Bundle\CommerceBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxRuleRepository extends BaseRepository implements ResourceRepositoryInterface
{
    use ResourceRepositoryTrait;
}
