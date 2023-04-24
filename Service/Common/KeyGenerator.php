<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;
use Ekyna\Component\Commerce\Common\Generator\StorageInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;

/**
 * Class KeyGenerator
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class KeyGenerator implements GeneratorInterface
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(
        private readonly ManagerRegistry $registry
    ) {
    }

    /**
     * @inheritDoc
     */
    public function setStorage(string|StorageInterface $storage, int $length = null): void
    {
        throw new LogicException('This generator does not need storage.');
    }

    /**
     * @inheritDoc
     */
    public function generate(object $subject): string
    {
        // TODO Prevent usage of a deleted subject's number
        // $this->manager->getFilters()->disable('softdeleteable');

        $class = get_class($subject);

        $manager = $this->registry->getManagerForClass($class);

        if (!$manager instanceof EntityManagerInterface) {
            throw new UnexpectedTypeException($manager, EntityManagerInterface::class);
        }

        $query = $manager
            ->createQuery(sprintf('SELECT o.id FROM %s o WHERE o.key = :key', $class))
            ->setMaxResults(1);

        do {
            $key = md5(random_bytes(16));
        } while (null !== $query->setParameter('key', $key)->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR));

        // $this->manager->getFilters()->enable('softdeleteable');

        return $key;
    }
}
