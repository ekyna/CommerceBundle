<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoicePaymentResolverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InvoicePaidTotalUpdateCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoicePaidTotalUpdateCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:invoice:update-paid-total';

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var InvoicePaymentResolverInterface
     */
    private $resolver;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var \Doctrine\DBAL\Driver\Statement
     */
    private $updateTotal;

    /**
     * @var \Doctrine\DBAL\Driver\Statement
     */
    private $updateRealTotal;


    /**
     * Constructor.
     *
     * @param EntityRepository                $repository
     * @param InvoicePaymentResolverInterface $resolver
     * @param EntityManagerInterface          $manager
     */
    public function __construct(
        EntityRepository $repository,
        InvoicePaymentResolverInterface $resolver,
        EntityManagerInterface $manager
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->resolver   = $resolver;
        $this->manager    = $manager;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Updates the invoices paid total')
            ->addArgument('id', InputArgument::OPTIONAL, 'To update a single order\'s invoices', 0);
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Updating invoices due dates');
        $output->writeln('');

        $metadata = $this->manager->getClassMetadata($this->repository->getClassName());
        $metadata->getTableName();

        /** @noinspection SqlResolve */
        $this->updateTotal = $this->manager->getConnection()->prepare(
            "UPDATE {$metadata->getTableName()} SET paid_total=:total WHERE id=:id LIMIT 1"
        );
        /** @noinspection SqlResolve */
        $this->updateRealTotal = $this->manager->getConnection()->prepare(
            "UPDATE {$metadata->getTableName()} SET real_paid_total=:total WHERE id=:id LIMIT 1"
        );

        $qb = $this->repository->createQueryBuilder('i');

        // Single order invoices case
        if (0 < $orderId = (int)$input->getArgument('id')) {
            $invoices = $qb
                ->andWhere($qb->expr()->eq('IDENTITY(i.order)', ':order_id'))
                ->addOrderBy('i.id', 'ASC')
                ->getQuery()
                ->setParameter('order_id', $orderId)
                ->getResult();

            $this->updateInvoices($invoices, $output);

            $output->writeln('');

            return 0;
        }

        // All invoices case
        $limit = 30;
        $page  = 0;

        $select = $qb
            ->addOrderBy('i.id', 'ASC')
            ->getQuery()
            ->setMaxResults($limit);

        do {
            $invoices = $select->setFirstResult($page * $limit)->execute();
            if (empty($invoices)) {
                break;
            }

            $this->updateInvoices($invoices, $output);

            $page++;
        } while (!empty($invoices));

        $output->writeln('');

        return 0;
    }

    /**
     * @param \Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface[] $invoices
     * @param OutputInterface                                            $output
     */
    private function updateInvoices(array $invoices, OutputInterface $output): void
    {
        foreach ($invoices as $invoice) {
            $number = $invoice->getNumber();

            $output->write(sprintf(
                '- <comment>%s</comment> %s ',
                $number,
                str_pad('.', 44 - mb_strlen($number), '.', STR_PAD_LEFT)
            ));

            $done = false;

            $total = $this->resolver->getPaidTotal($invoice);
            if (0 !== Money::compare($total, $invoice->getPaidTotal(), $invoice->getCurrency())) {
                if (!$this->updateTotal->execute(['total' => $total, 'id' => $invoice->getId()])) {
                    $output->writeln('<error>failure</error>');
                    continue;
                }
                $done = true;
            }

            $total = $this->resolver->getRealPaidTotal($invoice);
            if (0 !== Money::compare($total, $invoice->getRealPaidTotal(), $invoice->getCurrency())) {
                if (!$this->updateRealTotal->execute(['total' => $total, 'id' => $invoice->getId()])) {
                    $output->writeln('<error>failure</error>');
                    continue;
                }
                $done = true;
            }

            if ($done) {
                $output->writeln('<info>done</info>');
                continue;
            }

            $output->writeln('<comment>skipped</comment>');
        }

        $this->manager->clear();
    }
}
