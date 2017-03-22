<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Common\Generator\KeyGeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\KeySubjectInterface;

/**
 * Class KeyGenerator
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class KeyGenerator implements KeyGeneratorInterface
{
    const SELECT_QUERY = <<<DQL
SELECT o.id FROM %s o WHERE o.key = :key
DQL;

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
     * @inheritdoc
     */
    public function generate(KeySubjectInterface $subject)
    {
        if (null !== $subject->getKey()) {
            return $this;
        }

        // TODO (eventually) $this->manager->getFilters()->disable('softdeleteable');

        $class = get_class($subject);

        $query = $this->manager
            ->createQuery(sprintf(static::SELECT_QUERY, $class))
            ->setMaxResults(1);

        do {
            $key = substr(preg_replace('~[^a-zA-Z0-9]~', '', base64_encode(random_bytes(64))), 0, 32);
            $result = $query
                ->setParameter('key', $key)
                ->getOneOrNullResult(Query::HYDRATE_SCALAR);
        } while (null !== $result);

        $subject->setKey($key);

        // TODO (eventually) $this->manager->getFilters()->enable('softdeleteable');

        return $this;
    }
}
