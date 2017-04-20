<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Customer\Event\CustomerEvents;
use Ekyna\Component\Commerce\Customer\Repository\CustomerRepositoryInterface;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CustomerBirthdayCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CustomerBirthdayCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:customer:birthday';

    private CustomerRepositoryInterface $repository;
    private ResourceEventDispatcherInterface $dispatcher;
    private EntityManagerInterface $manager;


    public function __construct(
        CustomerRepositoryInterface $repository,
        ResourceEventDispatcherInterface $dispatcher,
        EntityManagerInterface $manager
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
    }

    protected function configure(): void
    {
        $this->setDescription("Dispatches the customer birthday event.\nDo not run more than once per day.");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $customers = $this->repository->findWithBirthdayToday();

        if (empty($customers)) {
            return Command::SUCCESS;
        }

        foreach ($customers as $customer) {
            $event = $this->dispatcher->createResourceEvent($customer);
            $this->dispatcher->dispatch($event, CustomerEvents::BIRTHDAY);
        }

        $this->manager->flush();

        return Command::SUCCESS;
    }
}
