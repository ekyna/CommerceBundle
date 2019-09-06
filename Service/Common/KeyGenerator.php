<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;
use Ekyna\Component\Commerce\Common\Generator\StorageInterface;
use Ekyna\Component\Commerce\Exception\LogicException;

/**
 * Class KeyGenerator
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class KeyGenerator implements GeneratorInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @inheritDoc
     */
    public function setStorage($storage, int $length = null): void
    {
        throw new LogicException("This generator does not need storage.");
    }

    /**
     * @inheritdoc
     */
    public function generate(object $subject): string
    {
        // TODO Prevent usage of a deleted subject's number
        // $this->manager->getFilters()->disable('softdeleteable');

        $class = get_class($subject);

        /** @noinspection SqlResolve */
        $query = $this->manager
            ->createQuery(sprintf("SELECT o.id FROM %s o WHERE o.key = :key", $class))
            ->setMaxResults(1);

        do {
            $key = substr(preg_replace('~[^a-zA-Z0-9]~', '', base64_encode(random_bytes(64))), 0, 32);
            $result = $query
                ->setParameter('key', $key)
                ->getOneOrNullResult(Query::HYDRATE_SCALAR);
        } while (null !== $result);

        // $this->manager->getFilters()->enable('softdeleteable');

        return $key;
    }
}
