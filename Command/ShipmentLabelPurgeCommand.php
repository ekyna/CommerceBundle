<?php

namespace Ekyna\Bundle\CommerceBundle\Command;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Order\Entity\OrderShipmentLabel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ShipmentLabelPurgeCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentLabelPurgeCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:commerce:shipment:purge-label')
            ->setDescription('Purges old labels.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Purging shipment labels...</comment>');

        $qb = $this->manager->createQueryBuilder();

        $date = (new \DateTime('-1 month'))->setTime(0, 0, 0);

        $count = $qb
            ->update(OrderShipmentLabel::class, 'l')
            ->set('l.content', ':value')
            ->where($qb->expr()->lt('l.updatedAt', ':date'))
            ->getQuery()
            ->setParameter('date', $date, Type::DATETIME)
            ->setParameter('value', null)
            ->execute();

        $output->writeln(sprintf('Purged <info>%d</info> shipment label(s).', $count));
    }
}
