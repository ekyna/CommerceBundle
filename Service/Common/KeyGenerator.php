<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;
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

        $query = $this->manager
            ->createQuery(sprintf("SELECT o.id FROM %s o WHERE o.key = :key", $class))
            ->setMaxResults(1);

        do {
            $key = md5(random_bytes(16));
        } while (null !== $query->setParameter('key', $key)->getOneOrNullResult(Query::HYDRATE_SCALAR));

        // $this->manager->getFilters()->enable('softdeleteable');

        return $key;
    }
}
