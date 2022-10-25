<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use DateTime;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\MessageIDValidation;
use Ekyna\Bundle\CommerceBundle\Service\Report\ReportMailer;
use Ekyna\Component\Commerce\Report\ReportConfig;
use Ekyna\Component\Commerce\Report\ReportRegistry;
use Ekyna\Component\Commerce\Report\Writer\XlsWriter;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Throwable;

use function sprintf;

/**
 * Class ReportGenerateCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ReportGenerateCommand extends Command
{
    protected static $defaultName        = 'ekyna:commerce:report:generate';
    protected static $defaultDescription = 'Generates and sends report about commerce statistics.';

    public function __construct(
        private readonly ReportMailer   $mailer,
        private readonly ReportRegistry $registry,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'The email to send to report to.')
            ->addArgument('from', InputArgument::REQUIRED, 'The date to report stat from.')
            ->addArgument('to', InputArgument::OPTIONAL, 'The date to report stat to (default now).')
            ->addOption('section', 's', InputOption::VALUE_REQUIRED
                | InputOption::VALUE_IS_ARRAY, 'The sections to report')
            ->addOption('test', 't', InputOption::VALUE_NONE, 'Test mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $config = $this->buildConfig($input);
        } catch (Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return Command::INVALID;
        }

        if (!$config->test && !$input->getOption('no-debug')) {
            $output->writeln('<comment>This command should be run with --no-debug option.</comment>');
        }

        $output->writeln(<<<TXT
Generating report from {$config->range->getStart()->format('Y-m-d')} to {$config->range->getEnd()->format('Y-m-d')}.
Sending it to {$config->email}.
TXT
        );

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Continue ?');
        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('Abort.');

            return Command::SUCCESS;
        }

        $this->mailer->send($config, new ConsoleLogger($output));

        return Command::SUCCESS;
    }

    private function buildConfig(InputInterface $input): ReportConfig
    {
        $config = new ReportConfig();

        try {
            $from = new DateTime($input->getArgument('from'));
        } catch (Throwable) {
            throw new Exception('Invalid «from» date.');
        }

        $config->range->setStart($from->setTime(0, 0));

        if (!empty($to = $input->getArgument('to'))) {
            try {
                $to = new DateTime($to);
            } catch (Throwable) {
                throw new Exception('Invalid «to» date.');
            }

            $config->range->setEnd($to->setTime(23, 59, 59, 999999));
        }

        if (empty($sections = $input->getOption('section'))) {
            $sections = $this->registry->getSectionNames();
        }

        foreach ($sections as $section) {
            $config->addSection($section);
        }

        $config->writer = XlsWriter::NAME;

        $email = $input->getArgument('email');

        $validator = new EmailValidator();
        if (!$validator->isValid($email, new MessageIDValidation())) {
            throw new Exception('Invalid email address.');
        }

        $config->email = $email;

        $config->test = $input->getOption('test');

        return $config;
    }
}
