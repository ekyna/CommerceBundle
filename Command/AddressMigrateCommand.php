<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\DBAL\Connection;
use Ekyna\Bundle\CommerceBundle\Service\Migration\AddressMigrator;
use libphonenumber\PhoneNumberUtil;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AddressMigrateCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AddressMigrateCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:address:migrate';

    public function __construct(
        private readonly Connection      $connection,
        private readonly PhoneNumberUtil $phoneNumberUtil
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $migrator = new AddressMigrator(
            $this->connection,
            $this->phoneNumberUtil
        );

        $logger = new class extends AbstractLogger {
            private OutputInterface $output;

            public function setOutput(OutputInterface $output)
            {
                $this->output = $output;
            }

            /**
             * @inheritDoc
             */
            public function log($level, $message, array $context = []): void
            {
                if ($level !== LogLevel::DEBUG) {
                    return;
                }
                $this->output->write($message);
            }
        };

        $logger->setOutput($output);

        $migrator->setLogger($logger);

        $migrator->migrate();

        return Command::SUCCESS;
    }
}
