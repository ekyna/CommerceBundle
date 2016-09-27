<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\NumberSubjectInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Model as ResourceModel;

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
     * Constructor.
     *
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(NumberSubjectInterface $subject)
    {
        if (null !== $subject->getNumber()) {
            return $this;
        }

        if (!$subject instanceof ResourceModel\ResourceInterface) {
            throw new InvalidArgumentException("Expected instance of ResourceInterface.");
        }
        if (!$subject instanceof ResourceModel\TimestampableInterface) {
            throw new InvalidArgumentException("Expected instance of TimestampableInterface.");
        }

        // TODO (eventually) $this->manager->getFilters()->disable('softdeleteable');

        if (null === $date = $subject->getCreatedAt()) {
            $subject->setCreatedAt($date = new \DateTime());
        }

        $class = get_class($subject);
        $notThisIdClause = null !== $subject->getId() ? 'AND o.id != :id' : '';

        $query = $this->manager->createQuery(
            "SELECT o.number " .
            "FROM $class o " .
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

        if (null !== $subject->getId()) {
            $query->setParameter('id', $subject->getId());
        }

        if (null !== $result = $query->getOneOrNullResult(Query::HYDRATE_SCALAR)) {
            $subject->setNumber((string)(intval($result['number']) + 1));
        } else {
            $subject->setNumber($date->format('ym') . str_pad('1', 5, '0', STR_PAD_LEFT));
        }

        // TODO (eventually) $this->manager->getFilters()->enable('softdeleteable');

        return $this;
    }
}
