<?php

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

    /**
     * @var CustomerRepositoryInterface
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
     * @param CustomerRepositoryInterface      $repository
     * @param ResourceEventDispatcherInterface $dispatcher
     * @param EntityManagerInterface           $manager
     */
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

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setDescription("Dispatches the customer birthday event.\nDo not run more than once per day.");
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $customers = $this->repository->findWithBirthdayToday();

        if (empty($customers)) {
            return;
        }

        foreach ($customers as $customer) {
            $event = $this->dispatcher->createResourceEvent($customer);
            /** @noinspection PhpParamsInspection */
            $this->dispatcher->dispatch(CustomerEvents::BIRTHDAY, $event);
        }

        $this->manager->flush();
    }
}
