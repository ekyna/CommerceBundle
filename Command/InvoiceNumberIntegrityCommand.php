<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function substr;

/**
 * Class InvoiceNumberIntegrityCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceNumberIntegrityCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:invoice:number-integrity';

    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly string          $orderInvoiceClass,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('from-date', InputArgument::OPTIONAL, 'The date to check invoices from')
            ->addArgument('to-date', InputArgument::OPTIONAL, 'The date to check invoices to')
            ->addOption('credit', 'c', InputOption::VALUE_NONE, 'Whether to check credits or invoices');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fromDate = new DateTime($input->getArgument('from-date') ?? (date('Y') . '-01-01'));
        $fromDate->setTime(0, 0);

        $toDate = new DateTime($input->getArgument('to-date') ?? '');
        $fromDate->setTime(23, 59, 59, 999999);

        $credit = (bool)$input->getOption('credit');

        $manager = $this
            ->registry
            ->getManagerForClass($this->orderInvoiceClass);

        if (!$manager instanceof EntityManager) {
            throw new UnexpectedTypeException($manager, EntityManager::class);
        }

        $dql = <<<DQL
        SELECT i.number
        FROM $this->orderInvoiceClass i
        WHERE i.createdAt BETWEEN :from_date AND :to_date
          AND i.credit = :credit
        ORDER BY i.number ASC
        DQL;

        $rows = $manager
            ->createQuery($dql)
            ->setParameter('credit', $credit)
            ->setParameter('from_date', $fromDate, Types::DATETIME_MUTABLE)
            ->setParameter('to_date', $toDate, Types::DATETIME_MUTABLE)
            ->getScalarResult();

        $holes = [];

        $previous = null;
        foreach ($rows as $row) {
            $number = (int)substr($row['number'], 1);
            if (null !== $previous && $previous + 1 !== $number) {
                $holes[] = "$previous <=> $number";
            }

            $previous = $number;
        }

        if (!empty($holes)) {
            foreach ($holes as $hole) {
                $output->writeln($hole);
            }
        }

        return Command::SUCCESS;
    }
}
