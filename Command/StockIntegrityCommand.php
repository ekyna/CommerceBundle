<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Stock\Integrity;
use Ekyna\Component\Commerce\Stock\Overflow\OverflowHandlerInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockSubjectUpdaterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
     */
    public function __construct(
        Connection $connection,
        StockSubjectUpdaterInterface $subjectUpdater,
        EntityManagerInterface $entityManager,
        OverflowHandlerInterface $overflowHandler
    ) {
        parent::__construct();

        $this->connection = $connection;
        $this->subjectUpdater = $subjectUpdater;
        $this->entityManager = $entityManager;
        $this->overflowHandler = $overflowHandler;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Checks the stock integrity.')
            ->addOption('fix', 'f', InputOption::VALUE_NONE, 'Whether to apply fixes')
            ->addOption('final', null, InputOption::VALUE_NONE, 'Whether to skip final check');
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
            new Integrity\UnitAssignChecker(),
            new Integrity\UnitOverflowChecker(
                $this->entityManager,
                $this->overflowHandler
            ),
        ];

        if (!$input->getOption('final')) {
            $this->checkers[] = new Integrity\FinalChecker();
        }

        if ($doFix = $input->getOption('fix')) {
            // Confirm
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('<question>Do you confirm you want to apply fixes ?</question>',
                false);
            if (!$helper->ask($input, $output, $question)) {
                return;
            }
        }

        $unitIds = [];

        $this->connection->beginTransaction();
        try {
            // Check
            foreach ($this->checkers as $checker) {
                $output->write($checker->getTitle());

                $checker->setConnection($this->connection);

                if (!$checker->check($output)) {
                    $output->writeln(" <info>ok</info>\n");

                    continue;
                }

                $output->writeln(" <error>error</error>\n");

                $checker->display($output);

                $output->writeln('');

                $checker->build($output);

                if (!$doFix) {
                    foreach ($checker->getActions() as $action) {
                        $output->writeln($action->getLabel());
                    }

                    $output->writeln('');

                    continue;
                }

                $output->writeln('');

                $checker->fix($output, $unitIds);

                $output->writeln('');
            }
            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            $this->connection->close();

            throw $e;
        }

        // Updates subjects
        $this->updateSubjects($output, $unitIds);

        $this->connection->close();
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
