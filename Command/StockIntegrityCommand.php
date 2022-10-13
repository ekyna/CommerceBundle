<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Stock\Integrity;
use Ekyna\Component\Commerce\Stock\Overflow\OverflowHandlerInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Throwable;

/**
 * Class StockIntegrityCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockIntegrityCommand extends Command
{
    protected static $defaultName        = 'ekyna:commerce:stock:integrity';
    protected static $defaultDescription = 'Performs various stock integrity checks.';

    public function __construct(
        private readonly Connection                   $connection,
        private readonly StockSubjectUpdaterInterface $subjectUpdater,
        private readonly EntityManagerInterface       $entityManager,
        private readonly OverflowHandlerInterface     $overflowHandler,
        private readonly MailerInterface              $mailer,
        private readonly string                       $email
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('fix', 'f', InputOption::VALUE_NONE, 'Whether to apply fixes')
            ->addOption('report', 'r', InputOption::VALUE_NONE, 'Whether to send email report');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array<int, Integrity\CheckerInterface> $checkers */
        $checkers = [
            new Integrity\UnitOrderedChecker(),
            new Integrity\UnitReceivedChecker(),
            new Integrity\UnitAdjustedChecker(),
            new Integrity\AssignmentChecker(),
            new Integrity\DuplicateChecker(),
            new Integrity\UnitAssignChecker(),
            new Integrity\UnitOverflowChecker(
                $this->entityManager,
                $this->overflowHandler
            ),
            new Integrity\FinalChecker(),
        ];

        $sendReport = $input->getOption('report');
        $applyFix = $input->getOption('fix');
        $noInteraction = $input->getOption('no-interaction');

        if ($sendReport && $applyFix) {
            throw new InvalidOptionException("You can't enable both report and fix.");
        }

        if ($sendReport) {
            $output = new BufferedOutput();
        } elseif ($applyFix) {
            if ($noInteraction) {
                throw new InvalidOptionException("You can't enable fix without interaction.");
            }

            // Confirm
            $question = new ConfirmationQuestion(
                '<question>Do you confirm you want to apply fixes ?</question>',
                false
            );
            if (!$this->getHelper('question')->ask($input, $output, $question)) {
                return Command::SUCCESS;
            }
        }

        $unitIds = [];
        $errors = false;

        $this->connection->beginTransaction();
        try {
            foreach ($checkers as $checker) {
                // Skip final check if there are some errors
                if ($checker instanceof Integrity\FinalChecker && $errors && !$applyFix) {
                    continue;
                }

                $output->write($checker->getTitle());

                $checker->setConnection($this->connection);

                if (!$checker->check($output)) {
                    $output->writeln(" <info>ok</info>\n");

                    continue;
                }

                $errors = true;

                $output->writeln(" <error>error</error>\n");

                $checker->display($output);

                $output->writeln('');

                $checker->build($output);

                if (!$applyFix) {
                    // Don't log actions into email report
                    if (!$sendReport) {
                        foreach ($checker->getActions() as $action) {
                            $output->writeln($action->getLabel());
                        }

                        $output->writeln('');
                    }

                    continue;
                }

                $output->writeln('');

                $checker->fix($output, $unitIds);

                $output->writeln('');
            }
            $this->connection->commit();
        } catch (Throwable $exception) {
            $this->connection->rollBack();

            $unitIds = []; // Clear unit ids to prevent subject update

            if ($sendReport) {
                $output->writeln("\n{$exception->getMessage()}\n");
                $errors = true;
            } else {
                $this->connection->close();

                throw $exception;
            }
        }

        // Updates subjects
        $this->updateSubjects($output, $unitIds);

        $this->connection->close();

        if (!($sendReport && $errors)) {
            return Command::SUCCESS;
        }

        $message = new Email();
        $message
            ->subject('Stock integrity report')
            ->text($output->fetch())
            ->from($this->email)
            ->to($this->email);

        $this->mailer->send($message);

        return Command::SUCCESS;
    }

    /**
     * Updates the fixed unit's subjects.
     *
     * @param OutputInterface $output
     * @param array           $unitIds
     */
    private function updateSubjects(OutputInterface $output, array $unitIds): void
    {
        if (empty($unitIds)) {
            return;
        }

        /** @var AbstractStockUnit[] $units */
        $units = $this
            ->entityManager
            ->getRepository(AbstractStockUnit::class)
            ->findBy(['id' => $unitIds]);

        foreach ($units as $unit) {
            $subject = $unit->getSubject();

            $output->write($subject . ' ... ');

            if ($this->subjectUpdater->update($subject)) {
                $this->entityManager->persist($subject);
                $output->writeln('<comment>updated</comment>');
            } else {
                $output->writeln('<info>ok</info>');
            }
        }

        $this->entityManager->flush();
    }
}
