<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

/**
 * Class FixVoucherNumberCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class FixVoucherNumberCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:order:fix_voucher_number';

    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly string          $orderClass
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var EntityManagerInterface $manager */
        $manager = $this->registry->getManagerForClass($this->orderClass);
        $connection = $manager->getConnection();

        $table = $manager->getClassMetadata($this->orderClass)->getTableName();

        $selectNumbersSql = <<<SQL
SELECT COUNT(o.id), o.voucher_number
FROM $table o
WHERE o.voucher_number IS NOT NULL
GROUP BY o.voucher_number
HAVING COUNT(o.id) > 1
ORDER BY COUNT(o.id) DESC;
SQL;

        $selectOrdersSql = <<<SQL
SELECT o.id
FROM $table o
WHERE o.voucher_number=:number
ORDER BY o.accepted_at, o.created_at
SQL;

        $updateOrderSql = <<<SQL
UPDATE $table o
SET o.voucher_number=:number
WHERE o.id=:id
SQL;

        $selectOrdersStmt = $connection->prepare($selectOrdersSql);
        $updateOrderStmt = $connection->prepare($updateOrderSql);

        $selectNumbersResult = $connection->executeQuery($selectNumbersSql);
        while (false !== $numberRow = $selectNumbersResult->fetchAssociative()) {
            $count = 0;

            $selectOrdersResult = $selectOrdersStmt->executeQuery([
                'number' => $numberRow['voucher_number'],
            ]);
            while (false !== $orderRow = $selectOrdersResult->fetchAssociative()) {
                $count++;

                $updateOrderStmt->executeQuery([
                    'number' => sprintf('%s (%s)', $numberRow['voucher_number'], $count),
                    'id'     => $orderRow['id'],
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
