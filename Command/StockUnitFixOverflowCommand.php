<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockUnit;
use Ekyna\Component\Commerce\Stock\Overflow\OverflowHandlerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StockUnitFixOverflowCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO Remove
 */
class StockUnitFixOverflowCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:stock:overflow';

    /**
     * @var OverflowHandlerInterface
     */
    private $overflowHandler;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;


    /**
     * Constructor.
     *
     * @param OverflowHandlerInterface $overflowHandler
     * @param EntityManagerInterface   $entityManager
     */
    public function __construct(OverflowHandlerInterface $overflowHandler, EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->overflowHandler = $overflowHandler;
        $this->entityManager   = $entityManager;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'The stock unit id.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = (int)$input->getArgument('id');

        if (0 >= $id) {
            $output->writeln("<error>Expected id greater than zero.</error>");

            return 1;
        }

        $unit = $this->entityManager->getRepository(AbstractStockUnit::class)->find($id);

        if (!$unit) {
            $output->writeln("<error>Stock unit not found.</error>");

            return 1;
        }

        $this->overflowHandler->handle($unit);
        $this->entityManager->flush();

        return 0;
    }
}
