<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Common\Util\DateUtil;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Resolver\DueDateResolverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InvoiceDueDateUpdateCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceDueDateUpdateCommand extends Command
{
    protected static $defaultName        = 'ekyna:commerce:invoice:update-due-date';
    protected static $defaultDescription = 'Updates the invoices due date';

    public function __construct(
        private readonly OrderInvoiceRepositoryInterface $repository,
        private readonly DueDateResolverInterface        $resolver,
        private readonly EntityManagerInterface          $manager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Updating invoices due dates');
        $output->writeln('');

        $class = $this->repository->getClassName();

        $qb = $this->manager->createQueryBuilder()->from($class, 'i');

        $metadata = $this->manager->getClassMetadata($class);
        $metadata->getTableName();

        /** @noinspection SqlResolve */
        $update = $this->manager->getConnection()->prepare(
            "UPDATE {$metadata->getTableName()} SET due_date=:date WHERE id=:id LIMIT 1"
        );

        $limit = 30;
        $page = 0;

        $select = $qb
            ->andWhere($qb->expr()->isNull('i.dueDate'))
            ->addOrderBy('i.id', 'ASC')
            ->getQuery()
            ->setMaxResults($limit);

        do {
            $invoices = $select->setFirstResult($page * $limit)->execute();
            $page++;

            /** @var InvoiceInterface $invoice */
            foreach ($invoices as $invoice) {
                $number = $invoice->getNumber();

                $output->write(sprintf(
                    '- <comment>%s</comment> %s ',
                    $number,
                    str_pad('.', 44 - mb_strlen($number), '.', STR_PAD_LEFT)
                ));

                $date = $this->resolver->resolveInvoiceDueDate($invoice);

                if (DateUtil::equals($date, $invoice->getDueDate())) {
                    $output->writeln('<comment>skipped</comment>');

                    continue;
                }

                try {
                    $update->executeQuery(['date' => $date->format('Y-m-d H:i:s'), 'id' => $invoice->getId()]);
                    $output->writeln('<info>done</info>');
                } catch (Exception $e) {
                    $output->writeln('<error>failure</error>');
                }
            }

            $this->manager->clear();
        } while (!empty($invoices));

        $output->writeln('');

        return Command::SUCCESS;
    }
}
