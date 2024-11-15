<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command\Migrate;

use Ekyna\Bundle\CommerceBundle\Service\Migration\SaleItemDescriptionMigrator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class SaleItemDescriptionCommand
 * @package Ekyna\Bundle\CommerceBundle\Command\Migrate
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemDescriptionCommand extends Command
{
    protected static $defaultName = 'ekyna:commerce:migrate:sale_item_description';

    public function __construct(
        private readonly SaleItemDescriptionMigrator $migrator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // TODO Confirm !

        $this->migrator->migrate();

        return Command::SUCCESS;
    }
}
