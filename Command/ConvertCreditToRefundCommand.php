<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Ekyna\Bundle\CommerceBundle\Service\Migration\CreditToRefundMigrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class ConvertCreditToRefundCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConvertCreditToRefundCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:credit:convert';

    /**
     * @var CreditToRefundMigrator
     */
    private $migrator;


    /**
     * Constructor.
     *
     * @param CreditToRefundMigrator $migrator
     */
    public function __construct(CreditToRefundMigrator $migrator)
    {
        parent::__construct();

        $this->migrator = $migrator;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->addArgument('id', InputArgument::OPTIONAL, 'From order id', 0)
            ->addOption('single', 's', InputOption::VALUE_NONE, 'Whether to convert a single order');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<error>This is a dangerous operation.</error>');

        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion("Convert all credits to refunds ?", false);
        if (!$helper->ask($input, $output, $question)) {
            return 0;
        }

        $this->migrator->setOutput($output);
        $this->migrator->migrate(
            (int)$input->getArgument('id'),
            (bool)$input->getOption('single')
        );

        return 0;
    }
}
