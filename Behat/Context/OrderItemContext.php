<?php

namespace Ekyna\Bundle\CommerceBundle\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvent;
use Ekyna\Component\Commerce\Common\Event\SaleItemEvents;

/**
 * Class OrderItemContext
 * @package Ekyna\Bundle\CommerceBundle\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItemContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @Given /^The following order items:$/
     *
     * @param TableNode $table
     */
    public function createOrderItems(TableNode $table)
    {
        $items = $this->castOrderItemsTable($table);

        $manager = $this->getContainer()->get('ekyna_commerce.order.manager');

        foreach ($items as $item) {
            $manager->persist($item);
        }

        $manager->flush();
        $manager->clear();
    }

    /**
     * @param TableNode $table
     *
     * @return array
     */
    private function castOrderItemsTable(TableNode $table)
    {
        /** @var \Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface $acmeProductRepository */
        $acmeProductRepository = $this->getContainer()->get('acme_product.product.repository');
        $subjectHelper = $this->getContainer()->get('ekyna_commerce.subject_helper');
        $orderRepository = $this->getContainer()->get('ekyna_commerce.order.repository');
        $itemRepository = $this->getContainer()->get('ekyna_commerce.order_item.repository');
        $dispatcher = $this->getContainer()->get('event_dispatcher');

        $items = [];
        foreach ($table as $row) {
            /** @var \Ekyna\Component\Commerce\Order\Model\OrderInterface $order */
            $order = $orderRepository->findOneBy(['number' => $row['order']]);
            if (null === $order) {
                throw new \InvalidArgumentException("Failed to find order with number '{$row['order']}'.");
            }

            /** @var \Ekyna\Component\Commerce\Order\Model\OrderItemInterface $item */
            $item = $itemRepository->createNew();
            $item
                ->setOrder($order)
                ->setQuantity($row['quantity']); // TODO Use packaging format

            if (isset($row['acme_product'])) {
                $acmeProduct = $acmeProductRepository->findOneBy(['reference' => $row['acme_product']]);
                if (null === $acmeProduct) {
                    throw new \InvalidArgumentException(
                        "Failed to find the acme product with reference '{$row['acme_product']}'."
                    );
                }
                $subjectHelper->assign($item, $acmeProduct);

                $dispatcher->dispatch(SaleItemEvents::BUILD, new SaleItemEvent($item));
            }

            $items[] = $item;
        }

        return $items;
    }
}
