<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Ekyna\Bundle\SettingBundle\Manager\SettingManagerInterface;
use Ekyna\Component\Commerce\Accounting\Export\AccountingExporterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Class AccountingExportCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccountingExportCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:accounting:export';

    public function __construct(
        private readonly AccountingExporterInterface $exporter,
        private readonly SettingManagerInterface     $settings,
        private readonly MailerInterface             $mailer
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this
            ->addArgument('date', InputArgument::REQUIRED, 'The date formatted as \'Y\' or \'Y-m\'.')
            ->addOption('email', 'm', InputOption::VALUE_OPTIONAL, 'The email to send the archive to.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getArgument('date');

        if (preg_match('~^[0-9]{4}$~', $date)) {
            $year = $date;
            $month = null;
        } elseif (preg_match('~^([0-9]{4})-([0-9]{2})$~', $date, $matches)) {
            $year = $matches[1];
            $month = $matches[2];
        } else {
            throw new InvalidArgumentException('Expected date as YYYY or YYYY-MM.');
        }

        $path = $this->exporter->export($year, $month);

        if (empty($email = $input->getOption('email'))) {
            $email = $this->settings->getParameter('general.admin_email');
        }

        $subject = sprintf(
            '[%s] Accounting %s',
            $this->settings->getParameter('general.site_name'),
            $date
        );

        $message = new Email();
        $message
            ->subject($subject)
            ->text('See attached file.')
            ->from($this->settings->getParameter('notification.no_reply'))
            ->to($email)
            ->attach(
                file_get_contents($path),
                "accounting_$date.zip",
                'application/zip'
            );

        $this->mailer->send($message);

        return Command::SUCCESS;
    }
}
