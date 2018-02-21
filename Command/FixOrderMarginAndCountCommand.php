<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FixOrderMarginAndCountCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FixOrderMarginAndCountCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;


    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:commerce:order:fix-margin-and-count')
            ->setDescription("Sets the order's margin total and items count.");
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $this->manager = $container->get('doctrine.orm.default_entity_manager');

        $marginCalculator = $container->get('ekyna_commerce.common.margin_calculator');

        $orderRepository = $this->getContainer()->get('ekyna_commerce.order.repository');

        $connection = $container->get('doctrine.dbal.default_connection');

        $result = $connection->query('SELECT o.id FROM commerce_order AS o WHERE o.items_count=0 OR o.margin_total=0;');

        $count = 0;
        while (false !== $id = $result->fetch(\PDO::FETCH_COLUMN)) {
            $changed = false;

            /** @var \Ekyna\Component\Commerce\Order\Model\OrderInterface $order */
            $order = $orderRepository->find($id);

            $name = $order->getNumber();
            $output->write(sprintf(
                '- %s %s ',
                $name,
                str_pad('.', 44 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));

            $marginAmount = 0;
            if (null !== $margin = $marginCalculator->calculateSale($order)) {
                $marginAmount = $margin->getAmount();
            }
            if ($order->getMarginTotal() != $marginAmount) {
                $order->setMarginTotal($marginAmount);
                $changed = true;
            }

            if ($order->getItemsCount() != $itemCount = $order->getItems()->count()) {
                $order->setItemsCount($itemCount);
                $changed = true;
            }

            if ($changed) {
                $output->writeln('<info>updated</info>');

                $this->manager->persist($order);
            } else {
                $output->writeln('<comment>up to date</comment>');
            }

            $count++;
            if ($count % 20 === 0) {
                $order = null;
                $this->flushAndClear();
                $this->printResourceUsage($output);
            }
        }
    }

    private function flushAndClear()
    {
        $this->manager->flush();
        $this->manager->clear();
    }

    /**
     * Prints the resource usage.
     *
     * @param OutputInterface $output
     */
    protected function printResourceUsage(OutputInterface $output)
    {
        $output->writeln(sprintf(
            "  Memory usage: %s | UOW size: %s",
            $this->getMemoryUsage(),
            $this->manager->getUnitOfWork()->size()
        ));
    }

    /**
     * Returns the human readable memory usage.
     *
     * @return string
     */
    protected function getMemoryUsage()
    {
        $size = memory_get_usage(true);
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];

        /** @noinspection PhpIllegalArrayKeyTypeInspection */
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }
}