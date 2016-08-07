<?php

namespace Ekyna\Bundle\CommerceBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Ekyna\Bundle\CommerceBundle\Entity\Order;
use Ekyna\Bundle\CommerceBundle\Event\OrderEvent;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Order\Entity\OrderAdjustment;
use Ekyna\Component\Commerce\Order\Entity\OrderItem;
use Ekyna\Component\Commerce\Order\Entity\OrderItemAdjustment;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;

/**
 * Class LoadOrderData
 * @package Ekyna\Bundle\CommerceBundle\DataFixtures\ORM
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LoadOrderData extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $om)
    {
        $dispatcher = $this->container->get('event_dispatcher');
        $repo = $this->container->get('ekyna_commerce.order.repository');

        /** @var \Ekyna\Component\Commerce\Common\Model\CurrencyInterface $currency */
        $currency = $this->container
            ->get('ekyna_commerce.currency.repository')
            ->findOneBy(['enabled' => true]);
        if (null === $currency) {
            throw new \RuntimeException('Failed to find enabled currency.');
        }


        for ($o = 0; $o < 6; $o++) {
            /** @var \Ekyna\Bundle\CommerceBundle\Entity\Order $order */
            $order = $repo->createNew();
            $order->setCurrency($currency);

            $this
                ->setIdentity($order)
                ->setInvoiceAddress($order)
                ->setDeliveryAddress($order)
                ->setItems($order);

            for ($a = 0; $a < rand(0, 2); $a++) {
                $order->addAdjustment($this->generateDiscount($a));
            }

            // TODO dispatch pre-create
            $dispatcher->dispatch(OrderEvents::PRE_CREATE, new OrderEvent($order));

            $om->persist($order);
            $om->flush();
        }
    }

    /**
     * @param Order $order
     *
     * @return LoadOrderData
     */
    private function setIdentity(Order $order)
    {
        $order
            ->setGender('mr')
            ->setFirstName($this->faker->firstName)
            ->setLastName($this->faker->lastName)
            ->setEmail($this->faker->safeEmail);

        return $this;
    }

    /**
     * @param Order $order
     *
     * @return LoadOrderData
     */
    private function setInvoiceAddress(Order $order)
    {
        $order->setInvoiceAddress($this->generateAddress($order));

        return $this;
    }

    /**
     * @param Order $order
     *
     * @return LoadOrderData
     */
    private function setDeliveryAddress(Order $order)
    {
        if (25 < rand(0, 100)) {
            $order->setSameAddress(true);
        } else {
            $order
                ->setSameAddress(false)
                ->setInvoiceAddress($this->generateAddress($order));
        }

        return $this;
    }

    /**
     * @param Order $order
     *
     * @return LoadOrderData
     */
    private function setItems(Order $order)
    {
        for ($i = 0; $i < 6; $i++) {
            $order->addItem($this->generateItem($i));
        }

        return $this;
    }

    /**
     * @param int $position
     * @param int $level
     *
     * @return OrderItem
     */
    private function generateItem($position, $level = 0)
    {
        $item = new OrderItem();
        $item
            ->setDesignation($this->faker->sentence(rand(3, 5), false))
            ->setReference($this->faker->bothify(strtoupper('????-####')))
            ->setQuantity(rand(1, 10))
            ->setPosition($position);

        if ($level < 1 && 0 == rand(0, 2)) {
            for ($c = 0; $c < rand(1, 3); $c++) {
                $item->addChild($this->generateItem($level + 1));
            }

            return $item;
        }

        // TODO randomly product based

        $position = 0;

        // Tax
        if (0 < $rate = rand(0, 2)) {
            $adjustment = new OrderItemAdjustment();
            $adjustment
                ->setDesignation(sprintf('TVA %s%%', $rate))
                ->setType(AdjustmentTypes::TYPE_TAXATION)
                ->setMode(AdjustmentModes::MODE_PERCENT)
                ->setAmount($rate)
                ->setPosition($position);

            $item->addAdjustment($adjustment);

            $position++;
        }

        $item
            ->setNetPrice(rand(1000, 10000) / 100)
            ->setWeight(rand(100, 1000));

        for ($a = $position; $a < rand(0, 2); $a++) {
            $item->addAdjustment($this->generateItemDiscount($a));
        }

        return $item;
    }

    private function generateItemDiscount($position)
    {
        $adjustment = new OrderItemAdjustment();
        $adjustment
            ->setDesignation($this->faker->sentence(rand(3, 5), false))
            ->setType(AdjustmentTypes::TYPE_DISCOUNT)
            ->setMode(50 < rand(0, 100) ? AdjustmentModes::MODE_FLAT : AdjustmentModes::MODE_PERCENT)
            ->setAmount(rand(5, 50))
            ->setPosition($position);

        return $adjustment;
    }

    private function generateDiscount($position)
    {
        $adjustment = new OrderAdjustment();
        $adjustment
            ->setDesignation($this->faker->sentence(rand(3, 5), false))
            ->setMode(50 < rand(0, 100) ? AdjustmentModes::MODE_FLAT : AdjustmentModes::MODE_PERCENT)
            ->setAmount(rand(5, 50))
            ->setPosition($position);

        return $adjustment;
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 100;
    }
}
