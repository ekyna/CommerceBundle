<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Ekyna\Component\Commerce\Cart\Repository\CartRepositoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CartPurgeCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartPurgeCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:cart:purge';

    private CartRepositoryInterface  $repository;
    private ResourceManagerInterface $manager;


    public function __construct(CartRepositoryInterface $repository, ResourceManagerInterface $manager)
    {
        parent::__construct();

        $this->repository = $repository;
        $this->manager = $manager;
    }

    protected function configure(): void
    {
        $this->setDescription('Removes the expired carts.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $debug = !$input->getOption('no-debug');
        $expiredCarts = $this->repository->findExpired();

        if (empty($expiredCarts)) {
            $debug && $output->writeln('No expired cart found.');

            return Command::SUCCESS;
        }

        foreach ($expiredCarts as $cart) {
            $number = $cart->getNumber();

            $debug && $output->write(sprintf(
                '- <comment>%s</comment> %s ',
                $number,
                str_pad('.', 44 - mb_strlen($number), '.', STR_PAD_LEFT)
            ));

            $event = $this->manager->delete($cart);

            if ($event->isPropagationStopped()) {
                $debug && $output->writeln('<error>failed</error>');
            } else {
                $debug && $output->writeln('<info>done</info>');
            }
        }

        return Command::SUCCESS;
    }
}
