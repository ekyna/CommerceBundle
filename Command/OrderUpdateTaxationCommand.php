<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Command;

use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Component\Commerce\Common\Builder\AdjustmentBuilderInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Contracts\Translation\TranslatorInterface;

use function is_null;
use function is_string;

/**
 * Class OrderUpdateTaxationCommand
 * @package Ekyna\Bundle\CommerceBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderUpdateTaxationCommand extends Command
{
    protected static $defaultName        = 'ekyna:commerce:order:taxation-update';
    protected static $defaultDescription = 'Update the order states.';

    public function __construct(
        private readonly OrderRepositoryInterface   $repository,
        private readonly AdjustmentBuilderInterface $adjustmentBuilder,
        private readonly TaxResolverInterface       $taxResolver,
        private readonly ResourceManagerInterface   $manager,
        private readonly TranslatorInterface        $translator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('number', InputArgument::REQUIRED, 'The order number');
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


        if ($this->updateSale($order)) {
            $event = $this->manager->update($order);
            if ($event->hasErrors()) {
                foreach ($event->getErrors() as $error) {
                    $output->writeln('<error>' . $error->trans($this->translator) . '</error>');
                }
            } else {
                $output->writeln("Taxation updated.");
            }
        } else {
            $output->writeln("Taxation not changed.");
        }

        return Command::SUCCESS;
    }

    private function updateSale(SaleInterface $sale): bool
    {
        $changed = $this->buildSaleItemsTaxationAdjustments($sale);

        return $this->buildSaleTaxationAdjustments($sale) || $changed;
    }

    /**
     * @see \Ekyna\Component\Commerce\Common\Builder\SaleAdjustmentBuilder::buildSaleTaxationAdjustments
     */
    public function buildSaleTaxationAdjustments(SaleInterface $sale): bool
    {
        $changed = false;

        $data = [];

        // For now, we assume that sale's taxation adjustments are only related to shipment.
        if (!($sale->isTaxExempt() || $sale->isSample()) && !is_null($taxable = $sale->getShipmentMethod())) {
            // Resolve taxes
            $data = $this->taxResolver->resolveTaxes($taxable, $sale);
        }

        return $this
                ->adjustmentBuilder
                ->buildAdjustments(AdjustmentTypes::TYPE_TAXATION, $sale, $data) || $changed;
    }

    /**
     * @see \Ekyna\Component\Commerce\Common\Builder\SaleAdjustmentBuilder::buildSaleItemsTaxationAdjustments
     */
    public function buildSaleItemsTaxationAdjustments(SaleInterface|SaleItemInterface $parent): bool
    {
        if ($parent instanceof SaleInterface) {
            $children = $parent->getItems();
        } else {
            $children = $parent->getChildren();
        }

        $change = false;

        foreach ($children as $child) {
            $change = $this->buildSaleItemTaxationAdjustments($child) || $change;

            if ($child->hasChildren()) {
                $change = $this->buildSaleItemsTaxationAdjustments($child) || $change;
            }
        }

        return $change;
    }

    /**
     * @see \Ekyna\Component\Commerce\Common\Builder\SaleAdjustmentBuilder::buildSaleItemTaxationAdjustments
     */
    public function buildSaleItemTaxationAdjustments(SaleItemInterface $item): bool
    {
        $data = [];

        $sale = $item->getRootSale();
        if (!$item->isPrivate() && !(null === $sale || $sale->isTaxExempt() || $sale->isSample())) {
            $data = $this->taxResolver->resolveTaxes($item, $sale);
        }

        return $this
            ->adjustmentBuilder
            ->buildAdjustments(AdjustmentTypes::TYPE_TAXATION, $item, $data);
    }
}
