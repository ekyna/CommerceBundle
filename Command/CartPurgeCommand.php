<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Ekyna\Component\Commerce\Cart\Repository\CartRepositoryInterface;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;
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
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ResourceOperatorInterface
     */
    private $cartOperator;


    /**
     * Constructor.
     *
     * @param CartRepositoryInterface   $cartRepository
     * @param ResourceOperatorInterface $cartOperator
     */
    public function __construct(CartRepositoryInterface $cartRepository, ResourceOperatorInterface $cartOperator)
    {
        parent::__construct();

        $this->cartRepository = $cartRepository;
        $this->cartOperator = $cartOperator;
    }

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
        $expiredCarts = $this->cartRepository->findExpired();

        if (empty($expiredCarts)) {
            $output->writeln('No expired cart found.');

            return;
        }

        /** @var \Ekyna\Component\Commerce\Cart\Model\CartInterface $cart */
        foreach ($expiredCarts as $cart) {
            $number = $cart->getNumber();
            $output->write(sprintf(
                '- <comment>%s</comment> %s ',
                $number,
                str_pad('.', 44 - mb_strlen($number), '.', STR_PAD_LEFT)
            ));

            $event = $this->cartOperator->delete($cart);
            if ($event->isPropagationStopped()) {
                $output->writeln('<error>failed</error>');
            } else {
                $output->writeln('<info>done</info>');
            }
        }
    }
}
