<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\DBAL\Connection;
use Swift_Mailer;
use Swift_SwiftException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

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
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $success;

    /**
     * @var string
     */
    private $failure;

    /**
     * Constructor.
     *
     * @param Connection   $connection
     * @param Swift_Mailer $mailer
     * @param string       $email
     */
    public function __construct(Connection $connection, Swift_Mailer $mailer, string $email)
    {
        parent::__construct();

        $this->connection = $connection;
        $this->mailer = $mailer;
        $this->email = $email;

        $this->success = sprintf(
            '<info>%s</info>',
            '\\' === DIRECTORY_SEPARATOR ? 'OK' : "\xE2\x9C\x94" /* HEAVY CHECK MARK (U+2714) */
        );
        $this->failure = sprintf(
            '<error>%s</error>',
            '\\' === DIRECTORY_SEPARATOR ? 'ERROR' : "\xE2\x9C\x98" /* HEAVY BALLOT X (U+2718) */
        );
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
            ->addOption('report', 'r', InputOption::VALUE_NONE, 'Whether to send email report');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
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

        $outstanding = $input->getOption('outstanding');
        $credit = $input->getOption('credit');

        if (!$outstanding && !$credit) {
            $outstanding = $credit = true;
        }

        $errors = false;

        if ($outstanding) {
            $errors |= $this->checkOutstandingBalance($output, $applyFix);
        }

        if ($credit) {
            $errors |= $this->checkCreditBalance($output, $applyFix);
        }

        $this->connection->close();

        if (!($sendReport && $errors)) {
            return;
        }

        $message =
            \Swift_Message::newInstance(
                'Customer balances integrity report',
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
     * Check the customers outstanding balances.
     *
     * @param OutputInterface $output
     * @param bool            $applyFix
     *
     * @return bool Whether errors are detected
     */
    private function checkOutstandingBalance(OutputInterface $output, bool $applyFix): bool
    {
        $output->writeln('Outstanding balances');

        $customers = $this->connection->query(<<<SQL
            SELECT id, number, company, first_name, last_name, parent_id, outstanding_balance, outstanding_limit 
            FROM commerce_customer
        SQL
        );

        $balanceQuery = $this->connection->prepare(<<<SQL
            SELECT SUM(p.real_amount)
            FROM (
                SELECT op.real_amount
                FROM commerce_order_payment op
                JOIN commerce_order o ON o.id=op.order_id
                JOIN commerce_payment_method AS opm ON opm.id=op.method_id
                WHERE o.customer_id=:customer_id
                  AND op.refund=0
                  AND op.state IN ('captured', 'authorized', 'expired')
                  AND opm.factory_name='outstanding_balance'
                UNION ALL
                SELECT qp.real_amount
                FROM commerce_quote_payment qp
                JOIN commerce_quote q ON q.id=qp.quote_id
                JOIN commerce_payment_method AS qpm ON qpm.id=qp.method_id
                WHERE q.customer_id=:customer_id
                  AND qp.refund=0
                  AND qp.state IN ('captured', 'authorized', 'expired')
                  AND qpm.factory_name='outstanding_balance'
                UNION ALL
                SELECT cp.real_amount
                FROM commerce_cart_payment cp
                JOIN commerce_cart c ON c.id=cp.cart_id
                JOIN commerce_payment_method AS cpm ON cpm.id=cp.method_id
                WHERE c.customer_id=:customer_id
                  AND cp.refund=0
                  AND cp.state IN ('captured', 'authorized', 'expired')
                  AND cpm.factory_name='outstanding_balance'
            ) as p
        SQL
        );

        $fixQuery = $this->connection->prepare(<<<SQL
            UPDATE commerce_customer
            SET outstanding_balance=:balance
            WHERE id=:customer_id LIMIT 1
        SQL
        );

        $rows = [];
        foreach ($customers as $customer) {
            $actual = -floatval($customer['outstanding_balance']);

            $balanceQuery->execute(['customer_id' => $customer['id']]);
            $expected = floatval($balanceQuery->fetchColumn(0));

            if (0 === bccomp($actual, $expected, 3)) {
                continue;
            }

            if (empty($identity = $customer['company'])) {
                $identity = $customer['first_name'] . ' ' . $customer['last_name'];
            }

            $row = [
                $customer['id'],
                $customer['number'],
                $identity,
                number_format($actual, 3),
                number_format($expected, 3),
            ];

            if ($applyFix) {
                if (0 < $customer['parent_id']) {
                    $row[] = '<comment>Has parent</comment>';
                } elseif (1 === bccomp($expected, floatval($customer['outstanding_limit']), 3)) {
                    $row[] = '<comment>Limit overflow</comment>';
                } else {
                    $success = $fixQuery->execute(['balance' => -$expected, 'customer_id' => $customer['id']]);
                    $row[] = $success ? $this->success : $this->failure;
                }
            }

            $rows[] = $row;
        }

        if (empty($rows)) {
            $output->writeln("<info>No error found.</info>\n");

            return false;
        }

        $headers = ['ID', 'Number', 'Identity', 'Actual', 'Expected'];
        if ($applyFix) {
            $headers[] = 'Fix';
        }

        $table = new Table($output);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->render();

        $output->writeln('');

        return true;
    }

    /**
     * Check the customers credit balances.
     *
     * @param OutputInterface $output
     * @param bool            $applyFix
     *
     * @return bool Whether errors are detected
     */
    private function checkCreditBalance(OutputInterface $output, bool $applyFix): bool
    {
        $output->writeln('Credit balances');

        $customers = $this->connection->query(
            'SELECT id, number, company, first_name, last_name, credit_balance FROM commerce_customer'
        );

        $paymentQuery = $this->connection->prepare(<<<SQL
            SELECT SUM(p.real_amount)
            FROM (
                SELECT p.real_amount
                FROM commerce_order_payment p
                JOIN commerce_order o ON o.id=p.order_id
                JOIN commerce_payment_method AS m ON m.id=p.method_id
                WHERE o.customer_id=:customer_id
                  AND p.refund=0
                  AND p.state IN ('captured', 'authorized', 'refunded')
                  AND m.factory_name='credit_balance'
                UNION ALL
                SELECT p.real_amount
                FROM commerce_quote_payment p
                JOIN commerce_quote q ON q.id=p.quote_id
                JOIN commerce_payment_method AS m ON m.id=p.method_id
                WHERE q.customer_id=:customer_id
                  AND p.refund=0
                  AND p.state IN ('captured', 'authorized', 'refunded')
                  AND m.factory_name='credit_balance'
                UNION ALL
                SELECT p.real_amount
                FROM commerce_cart_payment p
                JOIN commerce_cart c ON c.id=p.cart_id
                JOIN commerce_payment_method AS m ON m.id=p.method_id
                WHERE c.customer_id=:customer_id
                  AND p.refund=0
                  AND p.state IN ('captured', 'authorized', 'refunded')
                  AND m.factory_name='credit_balance'
            ) as p
        SQL
        );

        $refundQuery = $this->connection->prepare(<<<SQL
            SELECT SUM(p.real_amount)
            FROM (
                SELECT p.real_amount
                FROM commerce_order_payment p
                JOIN commerce_order o ON o.id=p.order_id
                JOIN commerce_payment_method AS m ON m.id=p.method_id
                WHERE o.customer_id=:customer_id
                  AND (
                      (p.refund=0 AND p.state IN ('refunded'))
                      OR (p.refund=1 AND p.state IN ('captured', 'authorized'))
                  )
                  AND m.factory_name='credit_balance'
                UNION ALL
                SELECT p.real_amount
                FROM commerce_quote_payment p
                JOIN commerce_quote q ON q.id=p.quote_id
                JOIN commerce_payment_method AS m ON m.id=p.method_id
                WHERE q.customer_id=:customer_id
                  AND (
                      (p.refund=0 AND p.state IN ('refunded'))
                      OR (p.refund=1 AND p.state IN ('captured', 'authorized'))
                  )
                  AND m.factory_name='credit_balance'
                UNION ALL
                SELECT p.real_amount
                FROM commerce_cart_payment p
                JOIN commerce_cart c ON c.id=p.cart_id
                JOIN commerce_payment_method AS m ON m.id=p.method_id
                WHERE c.customer_id=:customer_id
                  AND (
                      (p.refund=0 AND p.state IN ('refunded'))
                      OR (p.refund=1 AND p.state IN ('captured', 'authorized'))
                  )
                  AND m.factory_name='credit_balance'
            ) as p
        SQL
        );

        $fixQuery = $this->connection->prepare(<<<SQL
            UPDATE commerce_customer
            SET credit_balance=:balance
            WHERE id=:customer_id LIMIT 1
        SQL
        );

        $rows = [];
        foreach ($customers as $customer) {
            $actual = floatval($customer['credit_balance']);
            $expected = 0;

            $paymentQuery->execute(['customer_id' => $customer['id']]);
            $expected -= $payments = floatval($paymentQuery->fetchColumn(0));

            $refundQuery->execute(['customer_id' => $customer['id']]);
            $expected += $refunds = floatval($refundQuery->fetchColumn(0));

            if (0 === bccomp($actual, $expected, 3)) {
                continue;
            }

            if (empty($identity = $customer['company'])) {
                $identity = $customer['first_name'] . ' ' . $customer['last_name'];
            }

            $row = [
                $customer['id'],
                $customer['number'],
                $identity,
                number_format($actual, 3),
                number_format($expected, 3),
                number_format($payments, 3),
                number_format($refunds, 3),
            ];

            if ($applyFix) {
                if (-1 === bccomp($expected, 0, 3)) {
                    $success = $fixQuery->execute(['balance' => 0, 'customer_id' => $customer['id']]);
                    $row[] = ($success ? $this->success : $this->failure)
                        . ' <comment>Missing payments or refunds</comment>';
                } else {
                    $success = $fixQuery->execute(['balance' => $expected, 'customer_id' => $customer['id']]);
                    $row[] = $success ? $this->success : $this->failure;
                }
            }

            $rows[] = $row;
        }

        if (empty($rows)) {
            $output->writeln("<info>No error found.</info>\n");

            return false;
        }

        $headers = ['ID', 'Number', 'Identity', 'Actual', 'Expected', 'Payments', 'Refunds'];
        if ($applyFix) {
            $headers[] = 'Fix';
        }

        $table = new Table($output);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->render();

        $output->writeln('');

        return true;
    }
}
