<?php

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
    protected static $defaultName = 'ekyna:commerce:order:watch';

    /**
     * @var OrderRepositoryInterface
     */
    private $repository;

    /**
     * @var ResourceEventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var EntityManagerInterface
     */
    private $manager;


    /**
     * Constructor.
     *
     * @param OrderRepositoryInterface         $repository
     * @param ResourceEventDispatcherInterface $dispatcher
     * @param EntityManagerInterface           $manager
     */
    public function __construct(
        OrderRepositoryInterface $repository,
        ResourceEventDispatcherInterface $dispatcher,
        EntityManagerInterface $manager
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setDescription(
            "Dispatches a 'ORDER_COMPLETED' event for all orders completed yesterday.\n" .
            "Do not run more than once per day."
        );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orders = $this->repository->findCompletedYesterday();

        foreach ($orders as $order) {
            $event = $this->dispatcher->createResourceEvent($order);
            /** @noinspection PhpParamsInspection */
            $this->dispatcher->dispatch(OrderEvents::COMPLETED, $event);
        }

        $this->manager->flush();
    }
}
