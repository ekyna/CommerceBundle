<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Ekyna\Bundle\CommerceBundle\Factory\OrderFactory;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Repository\CartRepositoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Transformer\SaleTransformerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
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
    protected static $defaultName = 'ekyna:commerce:cart:transform';

    private CartRepositoryInterface  $cartRepository;
    private OrderFactory             $orderFactory;
    private SaleTransformerInterface $saleTransformer;


    public function __construct(
        CartRepositoryInterface  $cartRepository,
        OrderFactory             $orderFactory,
        SaleTransformerInterface $saleTransformer
    ) {
        parent::__construct();

        $this->cartRepository = $cartRepository;
        $this->orderFactory = $orderFactory;
        $this->saleTransformer = $saleTransformer;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Transforms the cart to an order.')
            ->addArgument('number', InputArgument::REQUIRED, 'The cart number');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getHelper('question');

        if (empty($input->getArgument('number'))) {
            $question = new Question('Cart number:');
            $question->setValidator(function ($answer) {
                if (!is_string($answer) || empty($answer)) {
                    throw new InvalidArgumentException(
                        'Please provide a cart number.'
                    );
                }

                return $answer;
            });
            $question->setMaxAttempts(3);

            $input->setArgument('number', $helper->ask($input, $output, $question));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Check arguments
        if (empty($number = $input->getArgument('number'))) {
            throw new InvalidArgumentException("<error>Empty 'number' argument.</error>");
        }

        /** @var CartInterface $cart */
        $cart = $this->cartRepository->findOneBy(['number' => $number]);
        if (null === $cart) {
            $output->writeln("<error>Cart not found for number '$number'.</error>");

            return Command::FAILURE;
        }

        $this->renderSale($cart, $output);

        $output->writeln('');

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $confirmation = new ConfirmationQuestion(
            '<question>Are you sure you want to transform this cart to a new order ?</question>',
            false
        );
        if (!$helper->ask($input, $output, $confirmation)) {
            $output->writeln('');
            $output->writeln('<comment>Abort by user.</comment>');

            return Command::SUCCESS;
        }

        $output->writeln('');

        /** @var OrderInterface $order */
        $order = $this->orderFactory->create(false);

        $event = $this->saleTransformer->initialize($cart, $order);
        if ($event->isPropagationStopped()) {
            $this->writeMessages($event, $output);

            $output->writeln('<error>Initialization failed.</error>');

            return Command::FAILURE;
        }

        if (null !== $event = $this->saleTransformer->transform()) {
            $this->writeMessages($event, $output);

            $output->writeln('<error>Transformation failed.</error>');

            return Command::FAILURE;
        }

        $output->writeln('<info>Success !</info>');
        $output->writeln('');

        $this->renderSale($order, $output);

        return Command::SUCCESS;
    }

    private function renderSale(SaleInterface $sale, OutputInterface $output): void
    {
        $table = new Table($output);
        $table->setRows([
            ['Number', $sale->getNumber()],
            ['State', $sale->getState()],
            ['Grand total', $sale->getGrandTotal()->toFixed(2)],
            ['Paid total', $sale->getPaidTotal()->toFixed(2)],
            ['Company', $sale->getCompany()],
            ['Name', $sale->getFirstName() . ' ' . $sale->getLastName()],
            ['Email', $sale->getEmail()],
        ]);
        $table->render();
    }

    private function writeMessages(ResourceEventInterface $event, OutputInterface $output): void
    {
        foreach ($event->getMessages(ResourceMessage::TYPE_ERROR) as $message) {
            $output->writeln($message->getMessage());
        }
    }
}
