<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
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

use function file_get_contents;
use function gc_collect_cycles;
use function in_array;
use function sprintf;

/**
 * Class OrderExportCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
#[AsCommand(
    name: 'ekyna:commerce:order:export',
    description: 'Exports orders to CSV files.',
)]
class OrderExportCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly OrderRepositoryInterface $repository,
        private readonly MailerInterface $mailer,
        private readonly string $reportEmail,
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
            'orders_%s_%s',
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        );

        if ($format === 'csv') {
            $file = new Csv($fileName);
            $mimeType = Csv::MIME_TYPE;
        } else {
            $file = new Xls($fileName);
            $mimeType = Xls::MIME_TYPE;
        }

        $file->addRow([
            'date',
            'number',
            'company',
            'title',
            'country',
            'total',
            'CA',
            'shipping',
            'margin',
            'done',
            'payment',
            'shipment',
            'invoice',
        ]);

        $page = 0;
        while (!empty($orders = $this->repository->findByAcceptedAt($range, $page, 30))) {
            foreach ($orders as $order) {
                $done = ShipmentStates::STATE_COMPLETED === $order->getShipmentState()
                && InvoiceStates::STATE_COMPLETED === $order->getInvoiceState()
                    ? 'yes'
                    : 'no';

                $company = $order->getCustomer()?->getCompany()
                    ?? $order->getCompany();

                $margin = $order->getMargin();

                $file->addRow([
                    $order->getAcceptedAt()->format('Y-m-d'),
                    $order->getNumber(),
                    $company,
                    $order->getTitle(),
                    $order->getInvoiceAddress()->getCountry()->getCode(),
                    $order->getGrandTotal()->toFixed(2),
                    $margin->getRevenueProduct()->toFixed(2),
                    $margin->getRevenueShipment()->toFixed(2),
                    $margin->getTotal(false)->toFixed(2),
                    $done,
                    $order->getPaymentState(),
                    $order->getShipmentState(),
                    $order->getInvoiceState(),
                ]);
            }

            $this->manager->clear();
            gc_collect_cycles();

            $page++;
        }

        $path = $file->close();

        $subject = sprintf(
            'Orders export from %s to %s',
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
