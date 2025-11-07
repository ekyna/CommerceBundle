<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssignerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class DetachSaleItemCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderDetachCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:order:detach';

    public function __construct(
        private readonly OrderRepositoryInterface   $orderRepository,
        private readonly EntityManagerInterface     $orderManager,
        private readonly StockUnitAssignerInterface $stockAssigner
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('number', InputArgument::REQUIRED, 'The order number');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $number = $input->getArgument('number');

        $order = $this->orderRepository->findOneByNumber($number);

        if (!$order) {
            $output->writeln("<error>Order $number not found.</error>");

            return 1;
        }

        if (OrderStates::isStockableState($order)) {
            $output->writeln("<error>Order $number is in stockable state.</error>");

            return 1;
        }

        // Confirmation
        $helper = new QuestionHelper();
        $question = new ConfirmationQuestion('Continue with this action?', false);
        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        foreach ($order->getItems() as $item) {
            $this->detachSaleItemRecursively($item);
        }

        $this->orderManager->persist($order);
        $this->orderManager->flush();

        $output->writeln("<info>Order $number detached.</info>");

        return 0;
    }

    private function detachSaleItemRecursively(OrderItemInterface $item): void
    {
        foreach ($item->getChildren() as $child) {
            $this->detachSaleItemRecursively($child);
        }

        $this->stockAssigner->detachOrderItem($item);
    }
}
