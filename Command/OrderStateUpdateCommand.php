<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Order\Resolver\OrderStateResolver;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class OrderStateUpdateCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderStateUpdateCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:order:update-state';

    private OrderRepositoryInterface $repository;
    private OrderStateResolver       $resolver;
    private ResourceManagerInterface $operator;
    private TranslatorInterface      $translator;


    public function __construct(
        OrderRepositoryInterface $repository,
        OrderStateResolver $resolver,
        ResourceManagerInterface $manager,
        TranslatorInterface $translator
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->resolver = $resolver;
        $this->operator = $manager;
        $this->translator = $translator;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Update the order states.')
            ->addArgument('number', InputArgument::REQUIRED, 'The order number');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getHelper('question');

        if (empty($input->getArgument('number'))) {
            $question = new Question('Order number:');
            $question->setValidator(function ($answer) {
                if (!is_string($answer) || empty($answer)) {
                    throw new InvalidArgumentException(
                        'Please provide a order number.'
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
            throw new InvalidArgumentException("Empty 'number' argument.");
        }

        /** @var OrderInterface $order */
        $order = $this->repository->findOneBy(['number' => $number]);
        if (null === $number) {
            $output->writeln("Order not found for number '$number'.");

            return Command::FAILURE;
        }

        $previousState = $order->getState();

        if ($this->resolver->resolve($order)) {
            $event = $this->operator->update($order);
            if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $output->writeln('<error>' . $error->trans($this->translator) . '</error>');
                }
            } else {
                $output->writeln(sprintf('<info>State changed (%s => %s)</info>', $previousState, $order->getState()));
            }
        } else {
            $output->writeln("State did not changed ($previousState).");
        }

        return Command::SUCCESS;
    }
}
