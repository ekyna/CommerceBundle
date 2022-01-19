<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\CommerceBundle\Service\Watcher\OutstandingWatcher;
use Ekyna\Bundle\SettingBundle\Manager\SettingManagerInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderPaymentRepositoryInterface;
use Ekyna\Component\Commerce\Payment\Repository\PaymentRepositoryInterface;
use Ekyna\Component\Commerce\Quote\Repository\QuotePaymentRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Class PaymentWatchCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentWatchCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:payment:watch';

    private OutstandingWatcher              $watcher;
    private OrderPaymentRepositoryInterface $orderPaymentRepository;
    private QuotePaymentRepositoryInterface $quotePaymentRepository;
    private EntityManagerInterface          $manager;
    private SettingManagerInterface         $setting;
    private MailerInterface                 $mailer;


    public function __construct(
        OutstandingWatcher              $watcher,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        QuotePaymentRepositoryInterface $quotePaymentRepository,
        EntityManagerInterface          $manager,
        SettingManagerInterface         $setting,
        MailerInterface                 $mailer
    ) {
        parent::__construct();

        $this->watcher = $watcher;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->quotePaymentRepository = $quotePaymentRepository;
        $this->manager = $manager;
        $this->setting = $setting;
        $this->mailer = $mailer;
    }

    protected function configure(): void
    {
        $this->setDescription('Watches the payment states (outstanding).');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $map = [
            'order' => $this->orderPaymentRepository,
            'quote' => $this->quotePaymentRepository,
        ];

        foreach ($map as $type => $repository) {
            $this->watch($repository, $type);
        }

        return Command::SUCCESS;
    }

    /**
     * Watches outstanding payments and sends report.
     */
    private function watch(PaymentRepositoryInterface $repository, string $type): void
    {
        if (!$this->watcher->watch($repository)) {
            return;
        }

        $this->manager->flush();
        $this->manager->clear();

        if (empty($report = $this->watcher->getReport())) {
            return;
        }

        $fromName = $this->setting->getParameter('notification.from_name') ?? '';
        $fromEmail = $this->setting->getParameter('notification.from_email');
        $toEmails = $this->setting->getParameter('notification.to_emails');

        $message = new Email();
        $message
            ->subject(ucfirst($type) . 'payments outstanding report')
            ->html($report)
            ->from(new Address($fromEmail, $fromName))
            ->to(...$toEmails);

        $this->mailer->send($message);
    }
}
