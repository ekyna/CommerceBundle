<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CartPurgeCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartPurgeCommand extends ContainerAwareCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:commerce:cart:purge')
            ->setDescription('Removes the expired carts.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $expiredCarts = $this
            ->getContainer()
            ->get('ekyna_commerce.cart.repository')
            ->findExpired();

        if (empty($expiredCarts)) {
            $output->writeln('No expired cart found.');

            return;
        }

        $operator = $this->getContainer()->get('ekyna_commerce.cart.operator');

        /** @var \Ekyna\Component\Commerce\Cart\Model\CartInterface $cart */
        foreach ($expiredCarts as $cart) {
            $number = $cart->getNumber();
            $output->write(sprintf(
                '- <comment>%s</comment> %s ',
                $number,
                str_pad('.', 44 - mb_strlen($number), '.', STR_PAD_LEFT)
            ));

            $event = $operator->delete($cart);
            if ($event->isPropagationStopped()) {
                $output->writeln('<error>failed</error>');
            } else {
                $output->writeln('<info>done</info>');
            }
        }
    }
}
