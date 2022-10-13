<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Ekyna\Bundle\CommerceBundle\Repository\TicketMessageRepository;
use Ekyna\Bundle\CommerceBundle\Service\Mailer\Mailer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SupportNotifyCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupportNotifyCommand extends Command
{
    protected static $defaultName        = 'ekyna:commerce:support:notify';
    protected static $defaultDescription = 'Sends emails to customers and administrators about created or updated ticket messages.';

    public function __construct(
        private readonly TicketMessageRepository $messageRepository,
        private readonly UserRepositoryInterface $adminRepository,
        private readonly EntityManagerInterface  $messageManager,
        private readonly Mailer                  $mailer
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->notifyCustomers();
        $this->notifyAdministrators();
        $this->notifyUnassigned();

        return Command::SUCCESS;
    }

    private function notifyCustomers(): void
    {
        $ticketIds = [];

        $messages = $this->messageRepository->findNotNotifiedForCustomers();

        if (empty($messages)) {
            return;
        }

        $count = 0;
        foreach ($messages as $message) {
            $ticketId = $message->getTicket()->getId();

            // Don't notify the customer twice for the same ticket.
            if (in_array($ticketId, $ticketIds, true)) {
                continue;
            }

            if (!$this->mailer->sendTicketMessageToCustomer($message)) {
                continue;
            }

            $ticketIds[] = $ticketId;

            // Mark the message as notified
            $message->setNotifiedAt(new DateTime());
            $this->messageManager->persist($message);

            $count++;

            if ($count % 20 === 0) {
                $this->messageManager->flush();
            }
        }

        if ($count % 20 !== 0) {
            $this->messageManager->flush();
        }
    }

    private function notifyAdministrators(): void
    {
        $administrators = $this->adminRepository->findAll();

        foreach ($administrators as $administrator) {
            $messages = $this->messageRepository->findNotNotifiedByInCharge($administrator);

            if (empty($messages)) {
                continue;
            }

            $this->mailer->sendTicketMessagesToAdmin($messages, $administrator);

            foreach ($messages as $message) {
                // Mark the message as notified
                $message->setNotifiedAt(new DateTime());
                $this->messageManager->persist($message);
            }

            $this->messageManager->flush();
        }
    }

    private function notifyUnassigned(): void
    {
        $messages = $this->messageRepository->findNotNotifiedAndUnassigned();

        if (empty($messages)) {
            return;
        }

        $this->mailer->sendTicketMessagesToAdmin($messages, null);

        foreach ($messages as $message) {
            // Mark the message as notified
            $message->setNotifiedAt(new DateTime());
            $this->messageManager->persist($message);
        }

        $this->messageManager->flush();
    }
}
