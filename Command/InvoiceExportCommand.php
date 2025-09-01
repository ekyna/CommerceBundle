<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoicePaymentResolverInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderInvoiceRepositoryInterface;
use Ekyna\Component\Resource\Helper\File\Csv;
use Ekyna\Component\Resource\Helper\File\Xls;
use Ekyna\Component\Resource\Model\DateRange;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use UnexpectedValueException;

use function array_keys;
use function array_values;
use function file_get_contents;
use function gc_collect_cycles;
use function sprintf;

/**
 * Class InvoiceExportCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
#[AsCommand(
    name: 'ekyna:commerce:invoice:export',
    description: 'Exports invoices to CSV files.',
)]
class InvoiceExportCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface          $manager,
        private readonly OrderInvoiceRepositoryInterface $repository,
        private readonly InvoicePaymentResolverInterface $resolver,
        private readonly MailerInterface                 $mailer,
        private readonly string                          $reportEmail,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('from', 'f', InputOption::VALUE_REQUIRED, 'The `from` date');
        $this->addOption('to', 't', InputOption::VALUE_REQUIRED, 'The `to` date');
        $this->addOption('format', null, InputOption::VALUE_REQUIRED, 'The file format', 'xls');
        $this->addOption(
            'email',
            null,
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            'The `email` to send export to'
        );
        $this->addOption('filename', null, InputOption::VALUE_REQUIRED, 'The exported file name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->manager->getConnection()->getConfiguration()->setSQLLogger(null);

        if (empty($from = $input->getOption('from'))) {
            $from = 'first day of previous month';
        }
        if (empty($to = $input->getOption('to'))) {
            $to = 'last day of previous month';
        }

        $from = new DateTime($from);
        $to = new DateTime($to);

        $range = new DateRange($from, $to);

        $format = $input->getOption('format');
        if (!in_array($format, ['csv', 'xls'], true)) {
            throw new UnexpectedValueException(sprintf("Expected 'xls' or 'csv', got '%s'", $format));
        }

        $fileName = $input->getOption('filename') ?? sprintf(
            'invoices_%s_%s',
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );

        $headers = [
            'date'           => 22,
            'number'         => 24,
            'type'           => 16,
            'country'        => 16,
            'total HT'       => 20,
            'customer'       => 50,
            'customer_group' => 80,
            'title'          => 64,
            'total TTC'      => 20,
        ];
        for ($i = 0; $i < 4; ++$i) {
            $headers += [
                "P$i:date"   => 22,
                "P$i:number" => 24,
                "P$i:mode"   => 40,
                "P$i:amount" => 20,
            ];
        }

        if ($format === 'csv') {
            $file = new Csv($fileName);
            $mimeType = Csv::MIME_TYPE;
        } else {
            $file = new Xls($fileName);
            $mimeType = Xls::MIME_TYPE;
            $file->setColumnsWidths(array_values($headers));
        }

        $file->setHeaders(array_keys($headers));

        $page = 0;
        while (!empty($invoices = $this->repository->findByCreatedAt($range, $page, 30))) {
            foreach ($invoices as $invoice) {
                $total = $invoice->getGoodsBase()
                    ->sub($invoice->getDiscountBase())
                    ->add($invoice->getShipmentBase());

                $data = [
                    $invoice->getCreatedAt()->format('Y-m-d'),
                    $invoice->getNumber(),
                    $invoice->isCredit() ? 'credit' : 'invoice',
                    $invoice->getOrder()->getInvoiceAddress()->getCountry()->getCode(),
                    ($invoice->isCredit() ? '-' : '') . $total->toFixed(2),
                    (string)$invoice->getOrder()->getCustomerGroup(),
                    $invoice->getOrder()->getCompany(),
                    $invoice->getOrder()->getTitle(),
                    ($invoice->isCredit() ? '-' : '') . $invoice->getGrandTotal()->toFixed(2),
                ];

                $invoicePayments = $this->resolver->resolve($invoice);
                foreach ($invoicePayments as $invoicePayment) {
                    if ($payment = $invoicePayment->getPayment()) {
                        $data[] = $payment->getCreatedAt()->format('Y-m-d');
                        $data[] = $payment->getNumber();
                        $data[] = $payment->getMethod()->getName();
                        $data[] = $invoicePayment->getRealAmount()->toFixed(2);
                    }
                    if ($invoice = $invoicePayment->getInvoice()) {
                        $data[] = $invoice->getCreatedAt()->format('Y-m-d');
                        $data[] = $invoice->getNumber();
                        $data[] = $invoice->isCredit() ? 'Credit' : 'Invoice';
                        $data[] = $invoicePayment->getRealAmount()->toFixed(2);
                    }
                }

                $file->addRow($data);
            }

            $this->manager->clear();
            gc_collect_cycles();

            $page++;
        }

        $path = $file->close();

        $subject = sprintf(
            'Invoices export from %s to %s',
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );

        if (empty($recipient = $input->getOption('email'))) {
            $recipient = [$this->reportEmail];
        }

        $message = new Email();
        $message->from($this->reportEmail);
        $message->to(...$recipient);
        $message->subject($subject);
        $message->text('See attachment');
        $message->attach(file_get_contents($path), $fileName . '.' . $format, $mimeType);

        $this->mailer->send($message);

        return Command::SUCCESS;
    }
}
