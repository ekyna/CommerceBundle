<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use DateTime;
use Doctrine\DBAL\Types\Types;
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
    protected static $defaultName        = 'ekyna:commerce:shipment:purge-label';
    protected static $defaultDescription = 'Purges outdated shipment labels.';

    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly string                 $retention = '6 months'
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<comment>Purging shipment labels...</comment>');

        $qb = $this->manager->createQueryBuilder();

        $date = (new DateTime('-' . $this->retention))->setTime(0, 0);

        $count = $qb
            ->update(OrderShipmentLabel::class, 'l')
            ->set('l.content', ':value')
            ->where($qb->expr()->lt('l.updatedAt', ':date'))
            ->getQuery()
            ->setParameter('date', $date, Types::DATETIME_MUTABLE)
            ->setParameter('value', null)
            ->execute();

        $output->writeln(sprintf('Purged <info>%d</info> shipment label(s).', $count));

        return Command::SUCCESS;
    }
}
