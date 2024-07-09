<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Decimal\Decimal;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Cart\Model\CartItemInterface;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Quote\Model\QuoteItemInterface;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Question\ConfirmationQuestion;

use function array_map;

/**
 * Class SaleItemUpdatePriceCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SaleItemUpdatePriceCommand extends Command
{
    protected static $defaultName        = 'ekyna:commerce:sale_item:update_price';
    protected static $defaultDescription = 'Updates a compound sale item price.';

    public function __construct(
        private readonly RepositoryFactoryInterface $repositoryFactory,
        private readonly ManagerFactoryInterface    $managerFactory,
        private readonly AmountCalculatorFactory    $calculatorFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'The id of the item to update')
            ->addArgument('price', InputArgument::REQUIRED, 'The price to set')
            ->addOption(
                'type',
                null,
                InputOption::VALUE_REQUIRED,
                'The sale item type (order, quote or cart)',
                'order'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $class = match ($input->getOption('type')) {
            'order' => OrderItemInterface::class,
            'quote' => QuoteItemInterface::class,
            'cart'  => CartItemInterface::class,
        };

        $repository = $this->repositoryFactory->getRepository($class);

        $item = $repository->find($id = (int)$input->getArgument('id'));

        if (!$item instanceof SaleItemInterface) {
            $output->writeln("Item #$id not found.");

            return Command::FAILURE;
        }

        $list = $this->buildList($item);

        $this->calculateNewPrices($list, new Decimal($input->getArgument('price')));

        $this->debug($output, $item, $list);

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Do you confirm update?', false);
        if (!$helper->ask($input, $output, $question)) {
            $output->write('Abord.');

            return Command::SUCCESS;
        }

        $this->updateNewPrices($list);

        $this->managerFactory->getManager($class)->save($item);

        $output->write('Updated.');

        return Command::SUCCESS;
    }

    /**
     * @param array<int, array{
     *     item: SaleItemInterface,
     *     quantity: Decimal,
     *     new_price: Decimal
     * }> $list
     */
    private function debug(OutputInterface $output, SaleItemInterface $item, array $list): void
    {
        $table = new Table($output);
        $table->setHeaders(['Référence', 'Quantity', 'Old price', 'New price']);
        $table->addRows(array_map(static function (array $row): array {
            return [
                $row['item']->getReference(),
                $row['quantity']->toFixed(),
                $row['item']->getNetPrice()->toFixed(2),
                $row['new_price']->toFixed(2),
            ];
        }, $list));
        $table->render();

        $currentTotal = new Decimal(0);
        $newTotal = new Decimal(0);

        foreach ($list as $row) {
            $currentTotal = $currentTotal->add($row['item']->getNetPrice()->mul($row['quantity']));
            $newTotal = $newTotal->add($row['new_price']->mul($row['quantity']));
        }

        $currency = $item->getRootSale()->getCurrency()->getCode();
        $calculator = $this->calculatorFactory->create($currency);
        $result = $calculator->calculateSaleItem($item);

        $table = new Table($output);
        $table->setHeaders(['Calculated', 'Current Total', 'New Total']);
        $table->addRow([
            $result->getGross()->toFixed(2),
            $currentTotal->toFixed(2),
            $newTotal->toFixed(2),
        ]);
        $table->render();
    }

    /**
     * @return array<int, array{
     *     item: SaleItemInterface,
     *     quantity: Decimal,
     *     new_price: Decimal
     * }>
     */
    private function buildList(SaleItemInterface $item): array
    {
        $list = [];

        $this->addChildren($list, $item->getChildren(), new Decimal(1));

        return $list;
    }

    /**
     * @param array<int, array{
     *     item: SaleItemInterface,
     *     quantity: Decimal,
     *     new_price: Decimal
     * }> $list
     */
    private function calculateNewPrices(array &$list, Decimal $newTotal): void
    {
        $currentTotal = new Decimal(0);
        foreach ($list as $row) {
            $currentTotal = $currentTotal->add($row['item']->getNetPrice()->mul($row['quantity']));
        }

        $ratio = $newTotal->div($currentTotal);

        foreach ($list as &$row) {
            $row['new_price'] = $row['item']->getNetPrice()->mul($ratio);
        }
    }

    private function updateNewPrices(array $list): void
    {
        foreach ($list as $row) {
            $row['item']->setNetPrice($row['new_price']);
        }
    }

    /**
     * @param Collection<SaleItemInterface> $items
     */
    private function addChildren(array &$list, Collection $items, Decimal $parentQuantity): void
    {
        foreach ($items as $item) {
            $quantity = $item->getQuantity()->mul($parentQuantity);
            if ($item->isCompound()) {
                $this->addChildren($list, $item->getChildren(), $quantity);

                continue;
            }

            $list[$item->getId()] = [
                'item'      => $item,
                'quantity'  => $quantity,
                'new_price' => $item->getNetPrice(),
            ];
        }
    }
}
