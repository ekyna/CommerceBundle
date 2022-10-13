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
    protected static $defaultName        = 'ekyna:commerce:cart:purge';
    protected static $defaultDescription = 'Removes the expired carts.';

    public function __construct(
        private readonly CartRepositoryInterface  $repository,
        private readonly ResourceManagerInterface $manager
    ) {
        parent::__construct();
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

            $debug
            && $output->write(sprintf(
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
