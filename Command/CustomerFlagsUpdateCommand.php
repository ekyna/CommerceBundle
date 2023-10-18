<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Helper\FlagHelper;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function gc_collect_cycles;
use function sprintf;

/**
 * Class CustomerFlagsUpdateCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerFlagsUpdateCommand extends Command
{
    protected static $defaultName        = 'ekyna:commerce:customer:update:flags';
    protected static $defaultDescription = 'Updates the customers flags.';

    private int $id = 0;

    public function __construct(
        private readonly ResourceRegistryInterface $registry,
        private readonly FlagHelper                $helper,
        private readonly EntityManagerInterface    $manager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::OPTIONAL, 'The customer ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (null === $id = $input->getArgument('id')) {
            $this->updateAll($output);

            return Command::SUCCESS;
        }

        if (null === $customer = $this->find((int)$id)) {
            $output->writeln("<error>Customer with ID $id not found.</error>");

            return Command::FAILURE;
        }

        $this->updateOne($customer, $output);

        $this->manager->flush();
        $this->manager->clear();

        return Command::SUCCESS;
    }

    private function updateOne(CustomerInterface $customer, OutputInterface $output): void
    {
        $output->write(
            sprintf(
                '<comment>%s</comment> ... ',
                $customer->getNumber()
            )
        );

        $changed = $this->updateProspect($customer);

        $changed = $this->updateInternational($customer) || $changed;

        if (!$changed) {
            $output->writeln('<comment>up to date</comment>');

            return;
        }

        $output->writeln('<info>updated</info>');

        $this->manager->persist($customer);
    }

    private function updateAll(OutputInterface $output): void
    {
        $count = 0;

        foreach ($this->findAll() as $customer) {
            $this->updateOne($customer, $output);

            $count++;
            if (0 !== $count % 20) {
                continue;
            }

            $this->manager->flush();
            $this->manager->clear();
            gc_collect_cycles();
        }
    }

    private function updateProspect(CustomerInterface $customer): bool
    {
        $prospect = $this->helper->isProspect($customer);

        if ($customer->isProspect() xor !$prospect) {
            return false;
        }

        $customer->setProspect($prospect);

        return true;
    }

    private function updateInternational(CustomerInterface $customer): bool
    {
        $international = $this->helper->isInternational($customer);

        if ($customer->isInternational() xor !$international) {
            return false;
        }

        $customer->setInternational($international);

        return true;
    }

    /**
     * @param int $id
     * @return CustomerInterface|null
     */
    private function find(int $id): ?CustomerInterface
    {
        $class = $this->registry->find('ekyna_commerce.customer')->getEntityClass();

        $query = $this
            ->manager
            ->createQuery(/** @lang DQL */ "SELECT i FROM $class i WHERE i.id = :id")
            ->setMaxResults(1);

        $query->setParameters(['id' => $id]);

        return $query->getOneOrNullResult();
    }

    /**
     * @return Generator<int, CustomerInterface>
     */
    private function findAll(): Generator
    {
        $class = $this->registry->find('ekyna_commerce.customer')->getEntityClass();

        $query = $this
            ->manager
            ->createQuery(/** @lang DQL */ "SELECT i FROM $class i WHERE i.id > :id ORDER BY i.id")
            ->setMaxResults(1);

        do {
            $query->setParameters(['id' => $this->id]);

            if (null === $customer = $query->getOneOrNullResult()) {
                break;
            }

            $this->id = $customer->getId();

            yield $customer;
        } while (true);
    }
}
