<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Ekyna\Bundle\CommerceBundle\Repository\ReportRequestRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReportRequestPurgeCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReportRequestPurgeCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:report_request:purge';

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly ReportRequestRepository $requestRepository,
        private readonly EntityManagerInterface $manager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::OPTIONAL, 'The user to purge the request of.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');

        if (!empty($email)) {
            $user = $this->userRepository->findOneByEmail($email);
            if (!$user instanceof UserInterface) {
                $output->writeln('<error>User not found.</error>');

                return Command::INVALID;
            }

            $request = $this->requestRepository->findOneByUser($user);
            if (null === $request) {
                return Command::SUCCESS;
            }
            $requests = [$request];
        } else {
            $requests = $this->requestRepository->findAll();
        }

        $this->purges($requests);

        return Command::SUCCESS;
    }

    private function purges(array $requests): void
    {
        foreach ($requests as $request) {
            $this->manager->remove($request);
        }

        $this->manager->flush();
    }
}
