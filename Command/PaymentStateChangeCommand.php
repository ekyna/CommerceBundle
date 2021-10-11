<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Ekyna\Bundle\CommerceBundle\Model\PaymentStates;
use Ekyna\Component\Commerce\Common\Locking\LockChecker;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Repository\PaymentRepositoryInterface;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
class PaymentStateChangeCommand extends ContainerAwareCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:commerce:payment:change-state')
            ->setDescription('Change the payment state.')
            ->addArgument('number', InputArgument::REQUIRED, 'The payment number')
            ->addArgument('state', InputArgument::REQUIRED, 'The payment new state')
            ->addOption('unlock', null, InputOption::VALUE_NONE, 'Whether to bypass locking');
    }

    /**
     * @inheritDoc
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        if (empty($input->getArgument('number'))) {
            $question = new Question('Payment number:');
            $question->setValidator(function ($answer) {
                if (!is_string($answer) || empty($answer)) {
                    throw new \InvalidArgumentException(
                        'Please provide a payment number.'
                    );
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

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
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

        // Find payment
        $payment = null;
        $operator = null;
        foreach (['order', 'quote', 'cart'] as $type) {
            $id = "ekyna_commerce.{$type}_payment.repository";
            $repository = $this->getContainer()->get($id);
            if (!$repository instanceof PaymentRepositoryInterface) {
                throw new \RuntimeException("Expected instance of " . PaymentRepositoryInterface::class);
            }

            if (null !== $payment = $repository->findOneBy(['number' => $number])) {
                $id = "ekyna_commerce.{$type}_payment.operator";
                $operator = $this->getContainer()->get($id);

                break;
            }
        }

        // Check if payment has been found
        if (!$payment instanceof PaymentInterface) {
            $output->writeln("<error>Payment with number $number not found.</error>");
            return;
        }
        if (!$operator instanceof ResourceOperatorInterface) {
            throw new \RuntimeException("Expected instance of " . ResourceOperatorInterface::class);
        }

        // Check that state change is needed
        if ($payment->getState() === $state) {
            $output->writeln("<info>Payment with number $number has already the state '$state'.</info>");
            return;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            "Change payment $number state from '{$payment->getState()}' to '$state' ?", false
        );
        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        if ($input->getOption('unlock')) {
            $this->getContainer()->get(LockChecker::class)->setEnabled(false);
        }

        $payment->setState($state);
        $event = $operator->update($payment);

        if ($event->hasErrors() || $event->isPropagationStopped()) {
            $output->writeln('<error>State change failed</error>');

            return;
        }

        $output->writeln('<info>State change succeeded</info>');
    }
}
