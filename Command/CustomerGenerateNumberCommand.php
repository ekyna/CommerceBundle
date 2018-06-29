<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class CustomerGenerateNumberCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGenerateNumberCommand extends ContainerAwareCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:commerce:customer:generate-number')
            ->setDescription('(Re)Generates the customer numbers.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('--- DISABLED ---');
        return;

        $output->writeln('<comment>!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!</comment>');
        $output->writeln('<comment>!!  This command will change all customer numbers.  !!</comment>');
        $output->writeln('<comment>!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!</comment>');

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("Do you want to continue ?", false);
        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $container = $this->getContainer();

        $connection = $container->get('doctrine.dbal.default_connection');

        $count = 0;
        $size = 20;

        $update = $connection->prepare("UPDATE commerce_customer SET number=:number WHERE id=:id LIMIT 1");

        do {
            $offset = $count*$size;
            $select = $connection->query("SELECT id, number, created_at FROM commerce_customer LIMIT $offset, $size");

            while (false !== $customer = $select->fetch(\PDO::FETCH_ASSOC)) {
                $oldNumber = $customer['number'];
                $newNumber = $this->generateNumber($customer);

                $output->writeln("[{$customer['id']}] $oldNumber => $newNumber");

                $update->execute([
                    'number' => $newNumber,
                    'id'     => $customer['id'],
                ]);
            }

            $count++;
        } while (0 < $select->rowCount());
    }

    /**
     * Generates the new customer number.
     *
     * @param array $customer
     *
     * @return string
     */
    protected function generateNumber(array $customer)
    {
        $number = intval(substr($customer['number'], 6));

        $prefix = (new \DateTime($customer['created_at']))->format('ym');

        return $prefix . str_pad($number, 7 - strlen($prefix), '0', STR_PAD_LEFT);
    }
}
