<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Ekyna\Component\Commerce\Cart\Repository\CartRepositoryInterface;
use Ekyna\Component\Commerce\Common\Transformer\SaleTransformerInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class CartTransformCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartTransformCommand extends Command
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SaleTransformerInterface
     */
    private $saleTransformer;


    /**
     * Constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param SaleTransformerInterface $saleTransformer
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        OrderRepositoryInterface $orderRepository,
        SaleTransformerInterface $saleTransformer
    ) {
        parent::__construct();

        $this->cartRepository = $cartRepository;
        $this->orderRepository = $orderRepository;
        $this->saleTransformer = $saleTransformer;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:commerce:cart:transform')
            ->setDescription('Transforms the cart to an order.')
            ->addArgument('number', InputArgument::REQUIRED, 'The cart number');
    }

    /**
     * @inheritDoc
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        if (empty($input->getArgument('number'))) {
            $question = new Question('Cart number:');
            $question->setValidator(function ($answer) {
                if (!is_string($answer) || empty($answer)) {
                    throw new \InvalidArgumentException(
                        'Please provide a cart number.'
                    );
                }

                return $answer;
            });
            $question->setMaxAttempts(3);

            $input->setArgument('number', $helper->ask($input, $output, $question));
        }
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Check arguments
        if (empty($number = $input->getArgument('number'))) {
            throw new \InvalidArgumentException("<error>Empty 'number' argument.</error>");
        }

        /** @var \Ekyna\Component\Commerce\Cart\Model\CartInterface $cart */
        $cart = $this->cartRepository->findOneBy(['number' => $number]);
        if (null === $number) {
            $output->writeln("<error>Cart not found for number '$number'.</error>");

            return;
        }

        $table = new Table($output);
        $table->setRows([
            ['Number', $cart->getNumber()],
            ['State', $cart->getState()],
            ['Grand total', $cart->getGrandTotal()],
            ['Paid total', $cart->getPaidTotal()],
            ['Company', $cart->getCompany()],
            ['Name', $cart->getFirstName() . ' ' . $cart->getLastName()],
            ['Email', $cart->getEmail()],
        ]);
        $table->render();

        $output->writeln("");

        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $confirmation = new ConfirmationQuestion(
            '<question>Are you sure you want to transform this cart to a new order ?</question>',
            false
        );
        if (!$helper->ask($input, $output, $confirmation)) {

            $output->writeln("");
            $output->writeln('<comment>Abort by user.</comment>');

            return;
        }

        $output->writeln("");

        $order = $this->orderRepository->createNew();

        $event = $this->saleTransformer->initialize($cart, $order);
        if ($event->isPropagationStopped()) {
            $this->writeMessages($event, $output);

            $output->writeln('<error>Initialization failed.</error>');

            return;
        }

        if (null !== $event = $this->saleTransformer->transform()) {
            $this->writeMessages($event, $output);

            $output->writeln('<error>Transformation failed.</error>');

            return;
        }

        $output->writeln('<info>Success !</info>');
        $output->writeln("");

        $table = new Table($output);
        $table->setRows([
            ['Number', $order->getNumber()],
            ['State', $order->getState()],
            ['Grand total', $order->getGrandTotal()],
            ['Paid total', $order->getPaidTotal()],
            ['Company', $order->getCompany()],
            ['Name', $order->getFirstName() . ' ' . $order->getLastName()],
            ['Email', $order->getEmail()],
        ]);
        $table->render();
    }

    /**
     * @param ResourceEventInterface $event
     * @param OutputInterface        $output
     */
    private function writeMessages(ResourceEventInterface $event, OutputInterface $output)
    {
        foreach ($event->getMessages(ResourceMessage::TYPE_ERROR) as $message) {
            $output->writeln($message->getMessage());
        }
    }
}
