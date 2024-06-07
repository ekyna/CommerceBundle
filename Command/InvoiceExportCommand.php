<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use DateTime;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Ekyna\Component\Resource\Helper\File\Csv;
use Ekyna\Component\Resource\Model\DateRange;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

use function file_get_contents;
use function sprintf;

/**
 * Class InvoiceExportCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceExportCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:invoice:export';

    public function __construct(
        private readonly OrderInvoiceRepositoryInterface $repository,
        private readonly MailerInterface                 $mailer,
        private readonly string                          $reportEmail,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('from', 'f', InputOption::VALUE_REQUIRED, 'The `from` date');
        $this->addOption('to', 't', InputOption::VALUE_REQUIRED, 'The `to` date');
        $this->addOption('email', null, InputOption::VALUE_REQUIRED, 'The `email` to send export to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (empty($from = $input->getOption('from'))) {
            $from = 'previous monday';
        }
        if (empty($to = $input->getOption('to'))) {
            $to = 'now';
        }

        $from = new DateTime($from);
        $to = new DateTime($to);

        $range = new DateRange($from, $to);

        $fileName = sprintf(
            'invoices_%s_%s.csv',
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );

        $csv = Csv::create($fileName);

        $csv->addRow([
            'date',
            'number',
            'type',
            'country',
            'total',
        ]);

        $page = 0;
        while (!empty($invoices = $this->repository->findByCreatedAt($range, $page, 30))) {
            foreach ($invoices as $invoice) {
                $total = $invoice->getGoodsBase()
                    ->sub($invoice->getDiscountBase())
                    ->add($invoice->getShipmentBase());

                $csv->addRow([
                    $invoice->getCreatedAt()->format('Y-m-d'),
                    $invoice->getNumber(),
                    $invoice->isCredit() ? 'credit' : 'invoice',
                    $invoice->getOrder()->getInvoiceAddress()->getCountry()->getCode(),
                    ($invoice->isCredit() ? '-' : '').$total->toFixed(2),
                ]);
            }

            $page++;
        }

        $path = $csv->close();

        $subject = sprintf(
            'Invoices export from %s to %s',
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );

        if (empty($recipient = $input->getOption('email'))) {
            $recipient = $this->reportEmail;
        }

        $message = new Email();
        $message->from($this->reportEmail);
        $message->to($recipient);
        $message->subject($subject);
        $message->text('See attachment');
        $message->attach(file_get_contents($path), $fileName, 'text/csv');

        $this->mailer->send($message);

        return Command::SUCCESS;
    }
}
