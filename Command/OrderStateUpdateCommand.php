<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Order\Resolver\OrderStateResolver;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class OrderStateUpdateCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderStateUpdateCommand extends Command
{
    /**
     * @var OrderRepositoryInterface
     */
    private $repository;

    /**
     * @var OrderStateResolver
     */
    private $resolver;

    /**
     * @var ResourceOperatorInterface
     */
    private $operator;


    /**
     * Constructor.
     *
     * @param OrderRepositoryInterface $repository
     * @param OrderStateResolver $resolver
     * @param ResourceOperatorInterface $operator
     */
    public function __construct(
        OrderRepositoryInterface $repository,
        OrderStateResolver $resolver,
        ResourceOperatorInterface $operator
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->resolver = $resolver;
        $this->operator = $operator;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:commerce:order:update-state')
            ->setDescription('Update the order states.')
            ->addArgument('number', InputArgument::REQUIRED, 'The order number');
    }

    /**
     * @inheritDoc
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        if (empty($input->getArgument('number'))) {
            $question = new Question('Order number:');
            $question->setValidator(function ($answer) {
                if (!is_string($answer) || empty($answer)) {
                    throw new \InvalidArgumentException(
                        'Please provide a order number.'
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
            throw new \InvalidArgumentException("Empty 'number' argument.");
        }

        /** @var \Ekyna\Bundle\CommerceBundle\Model\OrderInterface $order */
        $order = $this->repository->findOneBy(['number' => $number]);
        if (null === $number) {
            $output->writeln("Order not found for number '$number'.");

            return;
        }

        $previousState = $order->getState();

        if ($this->resolver->resolve($order)) {
            $event = $this->operator->update($order);
            if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $output->writeln("<error>$error</error>");
                }
            } else {
                $output->writeln(sprintf("<info>State changed (%s => %s)</info>", $previousState, $order->getState()));
            }
        } else {
            $output->writeln("State did not changed ($previousState).");
        }
    }
}
