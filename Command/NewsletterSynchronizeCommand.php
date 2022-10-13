<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Ekyna\Component\Commerce\Newsletter\Synchronizer\SynchronizerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class NewsletterSynchronizeCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NewsletterSynchronizeCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:newsletter:synchronize';

    public function __construct(
        private readonly SynchronizerRegistry $registry
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The synchronizer name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        if (!$this->registry->has($name)) {
            throw new InvalidArgumentException("Unknown synchronizer '$name'");
        }

        $this->registry->get($name)->synchronize(new ConsoleLogger($output));

        return Command::SUCCESS;
    }
}
