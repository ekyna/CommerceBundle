<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Ekyna\Bundle\CommerceBundle\Model\PaymentStates;
use Ekyna\Component\Commerce\Cart\Model\CartPaymentInterface;
use Ekyna\Component\Commerce\Common\Locking\LockChecker;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Repository\PaymentRepositoryInterface;
use Ekyna\Component\Commerce\Quote\Model\QuotePaymentInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class PaymentStateChangeCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentStateChangeCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:payment:change-state';

    private RepositoryFactoryInterface $repositoryFactory;
    private ManagerFactoryInterface    $managerFactory;
    private LockChecker                $lockChecker;


    public function __construct(
        RepositoryFactoryInterface $repositoryFactory,
        ManagerFactoryInterface    $managerFactory,
        LockChecker                $lockChecker
    ) {
        parent::__construct();

        $this->repositoryFactory = $repositoryFactory;
        $this->managerFactory = $managerFactory;
        $this->lockChecker = $lockChecker;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Change the payment state.')
            ->addArgument('number', InputArgument::REQUIRED, 'The payment number')
            ->addArgument('state', InputArgument::REQUIRED, 'The payment new state')
            ->addOption('unlock', null, InputOption::VALUE_NONE, 'Whether to bypass locking');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getHelper('question');

        if (empty($input->getArgument('number'))) {
            $question = new Question('Payment number:');
            $question->setValidator(function ($answer) {
                if (!is_string($answer) || empty($answer)) {
                    throw new InvalidArgumentException('Please provide a payment number.');
                }

                return $answer;
            });
            $question->setMaxAttempts(3);

            $input->setArgument('number', $helper->ask($input, $output, $question));
        }

        if (empty($input->getArgument('state'))) {
            $question = new ChoiceQuestion(
                'Please select the payment new state',
                PaymentStates::getConstants()
            );
            $question->setErrorMessage('State %s is invalid.');
            $question->setMaxAttempts(3);

            $input->setArgument('state', $helper->ask($input, $output, $question));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Check arguments
        if (empty($number = $input->getArgument('number'))) {
            throw new \InvalidArgumentException("Empty 'number' argument.");
        }
        if (empty($state = $input->getArgument('state'))) {
            throw new \InvalidArgumentException("Empty 'state' argument.");
        }

        // Validates state
        PaymentStates::isValid($state, true);

        $classes = [
            CartPaymentInterface::class,
            QuotePaymentInterface::class,
            OrderPaymentInterface::class,
        ];

        // Find payment
        $manager = null;
        foreach ($classes as $class) {
            $repository = $this->repositoryFactory->getRepository($class);
            if (!$repository instanceof PaymentRepositoryInterface) {
                throw new UnexpectedTypeException($repository, PaymentRepositoryInterface::class);
            }

            if (null === $payment = $repository->findOneBy(['number' => $number])) {
                continue;
            }

            $manager = $this->managerFactory->getManager($class);

            break;
        }

        // Check if payment has been found
        if (!$payment instanceof PaymentInterface) {
            $output->writeln("<error>Payment with number $number not found.</error>");

            return Command::FAILURE;
        }

        if (!$manager instanceof ResourceManagerInterface) {
            throw new UnexpectedTypeException($manager, ResourceManagerInterface::class);
        }

        // Check that state change is needed
        if ($payment->getState() === $state) {
            $output->writeln("<info>Payment with number $number has already the state '$state'.</info>");

            return Command::SUCCESS;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            "Change payment $number state from '{$payment->getState()}' to '$state' ?", false
        );
        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        if ($input->getOption('unlock')) {
            $this->lockChecker->setEnabled(false);
        }

        $payment->setState($state);

        $event = $manager->update($payment);

        if ($event->hasErrors() || $event->isPropagationStopped()) {
            $output->writeln('<error>State change failed</error>');

            return Command::FAILURE;
        }

        $output->writeln('<info>State change succeeded</info>');

        return Command::SUCCESS;
    }
}
