<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Ekyna\Bundle\CommerceBundle\Service\Watcher\OutstandingWatcher;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Payment\Repository\PaymentRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PaymentWatchCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentWatchCommand extends ContainerAwareCommand
{
    /**
     * @var OutstandingWatcher
     */
    private $watcher;


    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:commerce:payment:watch')
            ->setDescription('Watches the payment states (outstanding).');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->watcher = $this->getContainer()->get('ekyna_commerce.payment.outstanding_watcher');

        foreach (['order', 'quote'] as $type) {
            $id = "ekyna_commerce.{$type}_payment.repository";
            $repository = $this->getContainer()->get($id);
            if (!$repository instanceof PaymentRepositoryInterface) {
                throw new InvalidArgumentException("Expected instance of " . PaymentRepositoryInterface::class);
            }

            $this->watch($repository, $type);
        }
    }

    /**
     * Watches outstanding payments and sends report.
     *
     * @param PaymentRepositoryInterface $repository
     * @param string                     $type
     */
    private function watch(PaymentRepositoryInterface $repository, $type)
    {
        if (!$this->watcher->watch($repository)) {
            return;
        }

        $this->getContainer()->get('doctrine.orm.entity_manager')->flush();

        if (empty($report = $this->watcher->getReport())) {
            return;
        }

        $settings = $this->getContainer()->get('ekyna_setting.manager');
        $fromName = $settings->getParameter('notification.from_name');
        $fromEmail = $settings->getParameter('notification.from_email');
        $toEmails = $settings->getParameter('notification.to_emails');

        $message = new \Swift_Message();
        $message
            ->setSubject(ucfirst($type) . 'payments outstanding report')
            ->setBody($report, 'text/html')
            ->setFrom($fromEmail, $fromName)
            ->setTo($toEmails);

        $this->getContainer()->get('mailer')->send($message);
    }
}
