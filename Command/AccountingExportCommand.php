<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Component\Commerce\Accounting\Export\AccountingExporterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AccountingExportCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccountingExportCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:accounting:export';

    /**
     * @var AccountingExporterInterface
     */
    protected $exporter;

    /**
     * @var SettingsManagerInterface
     */
    protected $settings;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;


    /**
     * Constructor.
     *
     * @param AccountingExporterInterface $exporter
     * @param SettingsManagerInterface    $settings
     * @param \Swift_Mailer               $mailer
     */
    public function __construct(
        AccountingExporterInterface $exporter,
        SettingsManagerInterface $settings,
        \Swift_Mailer $mailer
    ) {
        parent::__construct();

        $this->exporter = $exporter;
        $this->settings = $settings;
        $this->mailer   = $mailer;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->addArgument('date', InputArgument::REQUIRED, "The date formatted as 'Y' or 'Y-m'.")
            ->addOption('email', 'm', InputOption::VALUE_OPTIONAL, "The email to send the archive to.");
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = $input->getArgument('date');

        if (preg_match('~^[0-9]{4}$~', $date)) {
            $year = $date;
            $month = null;
        } elseif (preg_match('~^([0-9]{4})-([0-9]{2})$~', $date, $matches)) {
            $year = $matches[1];
            $month = $matches[2];
        } else {
            throw new InvalidArgumentException("Expected date as YYYY or YYYY-MM.");
        }

        $path = $this->exporter->export($year, $month);

        if (empty($email = $input->getOption('email'))) {
            $email = $this->settings->getParameter('general.admin_email');
        }

        $subject = sprintf(
            "[%s] Accounting %s",
            $this->settings->getParameter('general.site_name'),
            $date
        );

        $message = \Swift_Message::newInstance($subject, "See attached file.");
        $message->setFrom($this->settings->getParameter('notification.no_reply'));
        $message->setTo($email);
        $message->attach(\Swift_Attachment::newInstance(
            file_get_contents($path),
            "accounting_$date.zip",
            'application/zip'
        ));

        $this->mailer->send($message);
    }
}
