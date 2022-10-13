<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Resolver\OrderStateResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use function array_keys;
use function gc_collect_cycles;
use function implode;
use function sprintf;

/**
 * Class OrdersStateCheckCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrdersStateCheckCommand extends Command
{
    protected static $defaultName        = 'ekyna:commerce:order:state-check';
    protected static $defaultDescription = 'Displays the orders whose state would change.';

    public function __construct(
        private readonly OrderStateResolver     $resolver,
        private readonly EntityManagerInterface $manager,
        private readonly string                 $orderClass
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('from', 'f', InputOption::VALUE_REQUIRED, 'The first order ID.', 9999999999)
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'The results limit.')
            ->addOption('persist', 'p', InputOption::VALUE_NONE, 'Whether to persist without confirm.')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Whether to skip persistence.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $from = (int)$input->getOption('from');
        $limit = (int)$input->getOption('limit');
        $persist = (bool)$input->getOption('persist');
        $dryRun = (bool)$input->getOption('dry-run');

        if ($persist && $dryRun) {
            throw new InvalidOptionException("You can't use both 'persist' and 'dry-run' options.");
        }

        $qb = $this->manager->createQueryBuilder();
        $query = $qb
            ->select('o')
            ->from($this->orderClass, 'o')
            ->andWhere($qb->expr()->lt('o.id', ':from'))
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->setMaxResults(20);

        $found = $updated = 0;

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Save these changes?', false);

        $orders = $query->setParameter('from', $from)->getResult();

        while (!empty($orders)) {
            /** @var OrderInterface $order */
            foreach ($orders as $order) {
                $from = $order->getId();

                $old = [
                    'state'    => $order->getState(),
                    'payment'  => $order->getPaymentState(),
                    'shipment' => $order->getShipmentState(),
                    'invoice'  => $order->getInvoiceState(),
                ];

                if (!$this->resolver->resolve($order)) {
                    continue;
                }

                $found++;

                $flags = [];
                if ($order->isSample()) {
                    $flags[] = 'S';
                }
                if ($order->isReleased()) {
                    $flags[] = 'R';
                }

                $output->writeln(sprintf(
                    '[%d] %s%s: ',
                    $order->getId(),
                    $order->getNumber(),
                    empty($flags) ? '' : ' (' . implode(', ', $flags) . ')'
                ));

                $new = [
                    'state'    => $order->getState(),
                    'payment'  => $order->getPaymentState(),
                    'shipment' => $order->getShipmentState(),
                    'invoice'  => $order->getInvoiceState(),
                ];

                foreach (array_keys($old) as $key) {
                    if ($old[$key] === $new[$key]) {
                        continue;
                    }

                    $output->writeln(sprintf('%s: %s -> %s', $key, $old[$key], $new[$key]));
                }

                if ($dryRun) {
                    if ($limit && $found >= $limit) {
                        break;
                    }

                    continue;
                }

                if ($persist || $helper->ask($input, $output, $question)) {
                    $this->manager->persist($order);
                    $this->manager->flush();
                    $updated++;
                }

                break;
            }

            unset($orders);
            $this->manager->clear();
            gc_collect_cycles();

            if ($limit && $found >= $limit) {
                break;
            }

            $orders = $query->setParameter('from', $from)->getResult();
        }

        $output->writeln("<info>Found $found orders having states changes, $updated updated</info>");
        $output->writeln("<comment>Last order id: $from</comment>");

        return Command::SUCCESS;
    }
}
