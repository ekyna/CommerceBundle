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
    protected static $defaultName = 'ekyna:commerce:support:notify';

    private TicketMessageRepository $messageRepository;
    private UserRepositoryInterface $adminRepository;
    private EntityManagerInterface  $messageManager;
    private Mailer                  $mailer;


    public function __construct(
        TicketMessageRepository $messageRepository,
        UserRepositoryInterface $adminRepository,
        EntityManagerInterface $messageManager,
        Mailer $mailer
    ) {
        $this->messageRepository = $messageRepository;
        $this->adminRepository = $adminRepository;
        $this->messageManager = $messageManager;
        $this->mailer = $mailer;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Sends emails to customers and administrators about created or update ticket messages.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->notifyCustomers();
        $this->notifyAdministrators();

        return Command::SUCCESS;
    }

    private function notifyCustomers(): void
    {
        $ticketIds = [];

        $messages = $this->messageRepository->findNotNotifiedForCustomers();

        $count = 0;
        foreach ($messages as $message) {
            // Do not notify the customer twice for the same ticket.
            $ticketId = $message->getTicket()->getId();
            if (!in_array($ticketId, $ticketIds, true)) {
                if (!$this->mailer->sendTicketMessageToCustomer($message)) {
                    continue;
                }

                $ticketIds[] = $ticketId;
            }

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
}
