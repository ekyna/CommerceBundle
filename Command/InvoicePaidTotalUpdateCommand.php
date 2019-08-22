<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoicePaymentResolverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InvoicePaidTotalUpdateCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoicePaidTotalUpdateCommand extends Command
{
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
        $this->resolver = $resolver;
        $this->manager = $manager;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:commerce:invoice:update-paid-total')
            ->setDescription('Updates the invoices paid total');
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

        /** @noinspection SqlResolve */
        $updateCurrent = $this->manager->getConnection()->prepare(
            "UPDATE {$metadata->getTableName()} SET paid_total=:total WHERE id=:id LIMIT 1"
        );
        /** @noinspection SqlResolve */
        $updateReal = $this->manager->getConnection()->prepare(
            "UPDATE {$metadata->getTableName()} SET real_paid_total=:total WHERE id=:id LIMIT 1"
        );

        $limit = 30;
        $page = 0;

        $select = $qb
            ->andWhere($qb->expr()->eq('i.type', ':type'))
            ->addOrderBy('i.id', 'ASC')
            ->getQuery()
            ->setParameter('type', InvoiceTypes::TYPE_INVOICE)
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

                $done = false;

                $total = $this->resolver->getPaidTotal($invoice);
                if (0 !== Money::compare($total, $invoice->getPaidTotal(), $invoice->getCurrency())) {
                    if (!$updateCurrent->execute(['total' => $total, 'id' => $invoice->getId()])) {
                        $output->writeln('<error>failure</error>');
                        continue;
                    }
                    $done = true;
                }

                $total = $this->resolver->getRealPaidTotal($invoice);
                if (0 !== Money::compare($total, $invoice->getRealPaidTotal(), $invoice->getCurrency())) {
                    if (!$updateReal->execute(['total' => $total, 'id' => $invoice->getId()])) {
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
        } while (!empty($invoices));

        $output->writeln('');
    }
}
