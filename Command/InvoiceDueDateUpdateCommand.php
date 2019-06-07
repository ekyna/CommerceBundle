<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Ekyna\Component\Commerce\Common\Util\DateUtil;
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
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var DueDateResolverInterface
     */
    private $resolver;

    /**
     * @var EntityManagerInterface
     */
    private $manager;


    /**
     * Constructor.
     *
     * @param EntityRepository         $repository
     * @param DueDateResolverInterface $resolver
     * @param EntityManagerInterface   $manager
     */
    public function __construct(
        EntityRepository $repository,
        DueDateResolverInterface $resolver,
        EntityManagerInterface $manager
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->resolver = $resolver;
        $this->manager = $manager;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:commerce:invoice:update-due-date')
            ->setDescription('Updates the invoices due date');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Updating invoices due dates');
        $output->writeln('');

        $qb = $this->repository->createQueryBuilder('i');

        $metadata = $this->manager->getClassMetadata($this->repository->getClassName());
        $metadata->getTableName();

        $connection = $this->manager->getConnection();

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

            /** @var \Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface $invoice */
            foreach ($invoices as $invoice) {
                $number = $invoice->getNumber();

                $output->write(sprintf(
                    '- <comment>%s</comment> %s ',
                    $number,
                    str_pad('.', 44 - mb_strlen($number), '.', STR_PAD_LEFT)
                ));

                $resolved = $this->resolver->resolveInvoiceDueDate($invoice);

                if (DateUtil::equals($resolved, $invoice->getDueDate())) {
                    $output->writeln('<comment>skipped</comment>');

                    continue;
                }

                if ($update->execute(['date' => $resolved->format('Y-m-d H:i:s'), 'id' => $invoice->getId()])) {
                    $output->writeln('<info>done</info>');
                } else {
                    $output->writeln('<error>failure</error>');
                }
            }

            $this->manager->clear();
        } while (!empty($invoices));

        $output->writeln('');
    }
}
