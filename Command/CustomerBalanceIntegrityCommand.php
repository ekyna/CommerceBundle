<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CustomerBalanceIntegrityCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerBalanceIntegrityCommand extends Command
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var bool
     */
    private $fix = false;


    /**
     * Constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct();

        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:commerce:customer:balance-integrity')
            ->setDescription('Checks the customer balances integrity.')
            ->addOption('outstanding', 'o', InputOption::VALUE_NONE, 'Check outstanding balances only')
            ->addOption('credit', 'c', InputOption::VALUE_NONE, 'Check credit balances only')
            ->addOption('fix', 'f', InputOption::VALUE_NONE, 'To fix balances')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'The email to sends the report to');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->fix = $input->getOption('fix');

        if (null !== $input->getOption('email')) {
            // TODO
            throw new \RuntimeException('Not yet supported');
        }

        $outstanding = $input->getOption('outstanding');
        $credit = $input->getOption('credit');

        if (!$outstanding && !$credit) {
            $outstanding = $credit = true;
        }

        if ($outstanding) {
            $this->checkOutstandingBalance();
        }
        if ($credit) {
            $this->checkCreditBalance();
        }
    }

    /**
     * Check the customers outstanding balances.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function checkOutstandingBalance()
    {
        $this->output->writeln('Outstanding balances');

        $customers = $this->connection->query(
            'SELECT id, email, parent_id, outstanding_balance, outstanding_limit FROM commerce_customer'
        );

        $balanceQuery = $this->connection->prepare(
            'SELECT SUM(p.amount) ' .
            'FROM commerce_order_payment AS p ' .
            'JOIN commerce_order AS o ON o.id=p.order_id ' .
            'JOIN commerce_payment_method AS m ON m.id=p.method_id ' .
            'WHERE o.customer_id=:customer_id ' .
            'AND p.state IN (\'captured\', \'expired\') ' .
            'AND m.factory_name=\'outstanding_balance\''
        );

        $fixQuery = $this->connection->prepare(
            'UPDATE commerce_customer ' .
            'SET outstanding_balance=:balance ' .
            'WHERE id=:customer_id LIMIT 1'
        );

        $headers = ['ID', 'Email', 'Actual', 'Expected'];
        if ($this->fix) {
            $headers[] = 'Fix';
        }

        $table = new Table($this->output);
        $table->setHeaders($headers);
        $found = false;

        $tick = sprintf('<info>%s</info>', '\\' === DIRECTORY_SEPARATOR ? 'OK' : "\xE2\x9C\x94" /* HEAVY CHECK MARK (U+2714) */);
        $cross = sprintf('<error>%s</error>', '\\' === DIRECTORY_SEPARATOR ? 'ERROR' : "\xE2\x9C\x98" /* HEAVY BALLOT X (U+2718) */);

        foreach ($customers as $customer) {
            $actual = -floatval($customer['outstanding_balance']);

            $balanceQuery->execute(['customer_id' => $customer['id']]);
            $expected = floatval($balanceQuery->fetchColumn(0));

            if (0 != bccomp($actual, $expected, 3)) {
                $row = [
                    $customer['id'],
                    $customer['email'],
                    number_format($actual, 3),
                    number_format($expected, 3),
                ];

                if ($this->fix) {
                    if (0 < $customer['parent_id']) {
                        $row[] = '<comment>Has parent</comment>';
                    } else if (1 === bccomp($expected, floatval($customer['outstanding_limit']), 3)) {
                        $row[] = '<comment>Limit overflow</comment>';
                    } else {
                        $success = $fixQuery->execute(['balance' => -$expected, 'customer_id' => $customer['id']]);
                        $row[] = $success ? $tick : $cross;
                    }
                }

                $table->addRow($row);
                $found = true;
            }
        }

        if ($found) {
            $table->render();
        } else {
            $this->output->writeln('<info>No error found.</info>');
        }

        $this->output->writeln('');
    }

    /**
     * Check the customers credit balances.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function checkCreditBalance()
    {
        $this->output->writeln('Credit balances');

        $customers = $this->connection->query('SELECT id, email, credit_balance FROM commerce_customer');

        $creditQuery = $this->connection->prepare(
            'SELECT SUM(i.grand_total) ' .
            'FROM commerce_order_invoice AS i ' .
            'JOIN commerce_order AS o ON o.id=i.order_id ' .
            'JOIN commerce_payment_method AS m ON m.id=i.payment_method_id ' .
            'WHERE o.customer_id=:customer_id ' .
            'AND i.type=\'credit\' '.
            'AND m.factory_name=\'credit_balance\''
        );

        $paymentQuery = $this->connection->prepare(
            'SELECT SUM(p.amount) ' .
            'FROM commerce_order_payment AS p ' .
            'JOIN commerce_order AS o ON o.id=p.order_id ' .
            'JOIN commerce_payment_method AS m ON m.id=p.method_id ' .
            'WHERE o.customer_id=:customer_id ' .
            'AND p.state IN (\'captured\') ' .
            'AND m.factory_name=\'credit_balance\''
        );

        // TODO payment refund type implementation
        $refundQuery = $this->connection->prepare(
            'SELECT SUM(p.amount) ' .
            'FROM commerce_order_payment AS p ' .
            'JOIN commerce_order AS o ON o.id=p.order_id ' .
            'JOIN commerce_payment_method AS m ON m.id=p.method_id ' .
            'WHERE o.customer_id=:customer_id ' .
            'AND p.state IN (\'refunded\') ' .
            'AND m.factory_name=\'credit_balance\''
        );

        $fixQuery = $this->connection->prepare(
            'UPDATE commerce_customer ' .
            'SET credit_balance=:balance ' .
            'WHERE id=:customer_id LIMIT 1'
        );

        $headers = ['ID', 'Email', 'Actual', 'Expected', 'Credits', 'Payments', 'Refunds'];
        if ($this->fix) {
            $headers[] = 'Fix';
        }

        $table = new Table($this->output);
        $table->setHeaders($headers);
        $found = false;

        $tick = sprintf('<info>%s</info>', '\\' === DIRECTORY_SEPARATOR ? 'OK' : "\xE2\x9C\x94" /* HEAVY CHECK MARK (U+2714) */);
        $cross = sprintf('<error>%s</error>', '\\' === DIRECTORY_SEPARATOR ? 'ERROR' : "\xE2\x9C\x98" /* HEAVY BALLOT X (U+2718) */);

        foreach ($customers as $customer) {
            $actual = floatval($customer['credit_balance']);
            $expected = 0;

            $creditQuery->execute(['customer_id' => $customer['id']]);
            $expected += $credits = floatval($creditQuery->fetchColumn(0));

            $paymentQuery->execute(['customer_id' => $customer['id']]);
            $expected -= $payments = floatval($paymentQuery->fetchColumn(0));

            $refunds = 0;
            // TODO payment refund type implementation
//            $refundQuery->execute(['customer_id' => $customer['id']]);
//            $expected += $refunds = floatval($refundQuery->fetchColumn(0));

            if (0 != bccomp($actual, $expected, 3)) {
                $row = [
                    $customer['id'],
                    $customer['email'],
                    number_format($actual, 3),
                    number_format($expected, 3),
                    number_format($credits, 3),
                    number_format($payments, 3),
                    number_format($refunds, 3),
                ];

                if ($this->fix) {
                    if (-1 === bccomp($expected, 0, 3)) {
                        $row[] = '<comment>Missing credits/payments</comment>';
                    } else {
                        $success = $fixQuery->execute(['balance' => $expected, 'customer_id' => $customer['id']]);
                        $row[] = $success ? $tick : $cross;
                    }
                }

                $table->addRow($row);
                $found = true;
            }
        }

        if ($found) {
            $table->render();
        } else {
            $this->output->writeln('<info>No error found.</info>');
        }

        $this->output->writeln('');
    }
}