<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Migration;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Class CustomersKeyGenerator
 * @package Ekyna\Bundle\CommerceBundle\Service\Migration
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomersKeyGenerator
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
        $this->manager   = $manager;
    }

    /**
     * Generates keys for customers.
     *
     * @return int The number of updated customers.
     */
    public function generate(): int
    {
        $connection = $this->manager->getConnection();

        $select = $connection->prepare('SELECT c.id FROM commerce_customer c WHERE c.secret IS NULL LIMIT 30');

        $check = $connection->prepare('SELECT c.id FROM commerce_customer c WHERE c.secret=:secret LIMIT 1');

        $update = $connection->prepare('UPDATE commerce_customer c SET c.secret=:secret WHERE c.id=:id LIMIT 1');

        $count = 0;

        do {
            $select->execute();

            if (empty($customers = $select->fetchAll(\PDO::FETCH_COLUMN, 0))) {
                break;
            }

            foreach ($customers as $id) {
                do {
                    $key = md5(random_bytes(16));
                    $check->execute(['secret' => $key]);
                } while (false !== $check->fetchColumn());

                $update->execute(['secret' => $key, 'id' => $id]);

                $count++;
            }
        } while (!empty($customers));

        return $count;
    }
}
