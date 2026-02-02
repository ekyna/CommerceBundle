<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Order\Export\OrderInvoiceLineExporter;
use Ekyna\Component\Resource\Model\DateRange;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

use function file_get_contents;
use function sprintf;

/**
 * Class InvoiceLineExportCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
#[AsCommand(
    name: 'ekyna:commerce:invoice_line:export',
    description: 'Exports invoice lines to an XLS file.',
)]
class InvoiceLineExportCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface   $manager,
        private readonly OrderInvoiceLineExporter $exporter,
        private readonly MailerInterface          $mailer,
        private readonly string                   $reportEmail,
    ) {
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this->addOption('from', 'f', InputOption::VALUE_REQUIRED, 'The `from` date');
        $this->addOption('to', 't', InputOption::VALUE_REQUIRED, 'The `to` date');
        $this->addOption(
            'email',
            null,
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            'The `email` to send export to'
        );
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

        $file = $this->exporter->export($range);

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
        $message->attach(file_get_contents($path), $file->getFilename(), $file->getMimeType());

        $this->mailer->send($message);

        return Command::SUCCESS;
    }
}
