<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Stat\Calculator\StatCalculatorInterface;
use Ekyna\Component\Commerce\Stat\Calculator\StatFilter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StatCalculateCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StatCalculateCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:stat:calculate';

    /**
     * @var StatCalculatorInterface
     */
    private $calculator;

    /**
     * @var EntityManagerInterface
     */
    private $manager;


    /**
     * Constructor.
     *
     * @param StatCalculatorInterface $calculator
     * @param EntityManagerInterface  $manager
     */
    public function __construct(StatCalculatorInterface $calculator, EntityManagerInterface $manager)
    {
        parent::__construct();

        $this->calculator = $calculator;
        $this->manager = $manager;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Calculates the statistics')
            ->addArgument('date', InputArgument::REQUIRED, "The date int the 'Y-m' format")
            ->addOption('subject', 's', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Subject.')
            ->addOption('exclude', null, InputOption::VALUE_NONE, 'Whether to exclude subjects.')
            ->addOption('day', null, InputOption::VALUE_NONE, 'Whether to calculate day stats.')
            ->addOption('month', null, InputOption::VALUE_NONE, 'Whether to calculate month stats (default).')
            ->addOption('year', null, InputOption::VALUE_NONE, 'Whether to calculate year stats.')
            ->addOption('skip', null, InputOption::VALUE_NONE, 'Whether to active skip mode.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $day = $input->getOption('day');
        $month = $input->getOption('month');
        $year = $input->getOption('year');

        if (!$day && !$month && !$year) {
            $month = true;
        } elseif (!($day xor $month xor $year)) {
            throw new InvalidOptionException("You must use only one of the --day, --month and --year options.");
        }

        if ($day) {
            $method = 'calculateDayOrderStats';
        } elseif ($year) {
            $method = 'calculateYearOrderStats';
        } elseif ($month) {
            $method = 'calculateMonthOrderStats';
        } else {
            throw new LogicException("Failed to determine which stat method to use.");
        }

        $date = $input->getArgument('date');
        try {
            $date = new \DateTime($date);
        } catch (\Throwable $e) {
            throw new \Exception("Please provide a valid date");
        }

        $filter = new StatFilter();
        $subjects = $input->getOption('subject');
        foreach ($subjects as $subject) {
            [$provider, $id] = explode(':', $subject);
            $filter->addSubject((string)$provider, (int)$id);
        }
        $filter->setExcludeSubjects($input->getOption('exclude'));

        $this->calculator->setSkipMode($input->getOption('skip'));

        $this->manager->getConnection()->getConfiguration()->setSQLLogger(null);

        $result = $this->calculator->{$method}($date, $filter);

        $table = new Table($output);
        $table->setHeaders([
            'Revenue',
            'Shipping',
            'Margin',
            'Orders',
            'Items',
            'Average',
        ]);
        $table->addRow([
            $result['revenue'],
            $result['shipping'],
            $result['margin'],
            $result['orders'],
            $result['items'],
            $result['average'],
        ]);

        $table->render();
    }
}
