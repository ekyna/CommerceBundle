<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Ekyna\Bundle\CommerceBundle\Service\Mailer\AddressHelper;
use Ekyna\Bundle\CommerceBundle\Service\Quote\QuoteReportGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

use function is_null;

/**
 * Class QuoteReportCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class QuoteReportCommand extends Command
{
    protected static $defaultName        = 'ekyna:commerce:report:quote';
    protected static $defaultDescription = 'Generates and sends report about obsolete quotes.';

    public function __construct(
        private readonly QuoteReportGenerator $generator,
        private readonly AddressHelper        $helper,
        private readonly TranslatorInterface  $translator,
        private readonly MailerInterface      $mailer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $reports = $this->generator->generate();

        if (empty($reports)) {
            return Command::SUCCESS;
        }

        $sender = $this->helper->getAdminHelper()->getNotificationSender();

        foreach ($reports as $email => $report) {
            $message = new Email();
            $message->from($sender);
            if (empty($email)) {
                $message->to($this->helper->getSalesAddress());
            } else {
                $message->to(new Address($email));
            }

            $message->subject($this->translator->trans('quote.outdated_projects_report', [], 'EkynaCommerce'));
            $message->html($report);

            $this->mailer->send($message);
        }

        return Command::SUCCESS;
    }
}
