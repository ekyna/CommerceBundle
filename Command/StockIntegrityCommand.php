<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Stock\Integrity;
use Ekyna\Component\Commerce\Stock\Overflow\OverflowHandlerInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Swift_Mailer;
use Swift_SwiftException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class StockIntegrityCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockIntegrityCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:stock:integrity';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var StockSubjectUpdaterInterface
     */
    private $subjectUpdater;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var OverflowHandlerInterface
     */
    private $overflowHandler;

    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $email;

    /**
     * @var Integrity\CheckerInterface[]
     */
    private $checkers;


    /**
     * Constructor.
     *
     * @param Connection                   $connection
     * @param StockSubjectUpdaterInterface $subjectUpdater
     * @param EntityManagerInterface       $entityManager
     * @param OverflowHandlerInterface     $overflowHandler
     * @param Swift_Mailer                 $mailer
     * @param string                       $email
     */
    public function __construct(
        Connection $connection,
        StockSubjectUpdaterInterface $subjectUpdater,
        EntityManagerInterface $entityManager,
        OverflowHandlerInterface $overflowHandler,
        Swift_Mailer $mailer,
        string $email
    ) {
        parent::__construct();

        $this->connection = $connection;
        $this->subjectUpdater = $subjectUpdater;
        $this->entityManager = $entityManager;
        $this->overflowHandler = $overflowHandler;
        $this->mailer = $mailer;
        $this->email = $email;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Checks the stock integrity.')
            ->addOption('fix', 'f', InputOption::VALUE_NONE, 'Whether to apply fixes')
            ->addOption('report', 'r', InputOption::VALUE_NONE, 'Whether to send email report');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkers = [
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
                return;
            }
        }

        $unitIds = [];
        $errors = false;

        $this->connection->beginTransaction();
        try {
            foreach ($this->checkers as $checker) {
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
        } catch (\Throwable $e) {
            $this->connection->rollBack();

            $unitIds = []; // Clear unit ids to prevent subject update

            if ($sendReport) {
                $output->writeln("\n{$e->getMessage()}\n");
                $errors = true;
            } else {
                $this->connection->close();
                throw $e;
            }
        }

        // Updates subjects
        $this->updateSubjects($output, $unitIds);

        $this->connection->close();

        if (!($sendReport && $errors)) {
            return;
        }

        $message =
            \Swift_Message::newInstance(
                'Stock integrity report',
                $output->fetch(),
                'text/plain'
            )
            ->setFrom($this->email)
            ->setTo($this->email);

        try {
            $this->mailer->send($message);
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (Swift_SwiftException $e) {
            // In case transport has bad configuration.
        }
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

            $output->write((string)$subject . ' ... ');

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
