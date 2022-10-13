<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class OrderWatchCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderWatchCommand extends Command
{
    protected static $defaultName        = 'ekyna:commerce:order:watch';
    protected static $defaultDescription = 'Dispatches a \'ORDER_COMPLETED\' event for all orders completed yesterday. Do not run more than once per day.';

    public function __construct(
        private readonly OrderRepositoryInterface         $repository,
        private readonly ResourceEventDispatcherInterface $dispatcher,
        private readonly EntityManagerInterface           $manager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $orders = $this->repository->findCompletedYesterday();

        foreach ($orders as $order) {
            $event = $this->dispatcher->createResourceEvent($order);
            $this->dispatcher->dispatch($event, OrderEvents::COMPLETED);
        }

        $this->manager->flush();

        return Command::SUCCESS;
    }
}
