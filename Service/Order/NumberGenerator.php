<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Order;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Order\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;

/**
 * Class NumberGenerator
 * @package Ekyna\Bundle\CommerceBundle\Service\Order
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NumberGenerator implements NumberGeneratorInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var string
     */
    private $orderClass;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface $manager
     * @param string                 $orderClass
     */
    public function __construct(EntityManagerInterface $manager, $orderClass)
    {
        $this->manager = $manager;
        $this->orderClass = $orderClass;
    }

    /**
     * {@inheritdoc}
     */
    public function generateNumber(OrderInterface $order)
    {
        if (null !== $order->getNumber()) {
            return $this;
        }

        // TODO (eventually) $this->manager->getFilters()->disable('softdeleteable');

        if (null === $date = $order->getCreatedAt()) {
            $order->setCreatedAt($date = new \DateTime());
        }

        $notThisIdClause = null !== $order->getId() ? 'AND o.id != :id' : '';

        $query = $this->manager->createQuery(
            "SELECT o.number " .
            "FROM $this->orderClass o " .
            "WHERE YEAR(o.createdAt) = :year " .
            "  AND MONTH(o.createdAt) = :month " .
            "  AND o.number IS NOT NULL " .
            "  $notThisIdClause " .
            "ORDER BY o.number DESC"
        );
        $query
            ->setMaxResults(1)
            ->setParameter('year', $date->format('Y'))
            ->setParameter('month', $date->format('m'));

        if (null !== $order->getId()) {
            $query->setParameter('id', $order->getId());
        }

        if (null !== $result = $query->getOneOrNullResult(Query::HYDRATE_SCALAR)) {
            $order->setNumber((string)(intval($result['number']) + 1));
        } else {
            $order->setNumber($date->format('ym') . str_pad('1', 5, '0', STR_PAD_LEFT));
        }

        // TODO (eventually) $this->manager->getFilters()->enable('softdeleteable');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function generateKey(OrderInterface $order)
    {
        if (null !== $order->getKey()) {
            return $this;
        }

        // TODO (eventually) $this->manager->getFilters()->disable('softdeleteable');

        $query = $this->manager->createQuery(
            "SELECT o.id " .
            "FROM $this->orderClass o " .
            "WHERE o.key = :key"
        );
        $query->setMaxResults(1);

        do {
            $key = substr(preg_replace('~[^a-zA-Z\d]~', '', base64_encode(random_bytes(64))), 0, 32);
            $result = $query
                ->setParameter('key', $key)
                ->getOneOrNullResult(Query::HYDRATE_SCALAR);
        } while (null !== $result);

        $order->setKey($key);

        // TODO (eventually) $this->manager->getFilters()->enable('softdeleteable');

        return $this;
    }
}
