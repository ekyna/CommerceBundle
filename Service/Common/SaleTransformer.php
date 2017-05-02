<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Listener\UploadableListener;
use Ekyna\Component\Commerce\Common\Transformer\SaleTransformer as BaseTransformer;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;

/**
 * Class SaleTransformer
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleTransformer extends BaseTransformer implements SaleTransformerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;


    /**
     * Constructor.
     *
     * @param SaleFactoryInterface     $saleFactory
     * @param EntityManagerInterface   $manager
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        SaleFactoryInterface $saleFactory,
        EntityManagerInterface $manager,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($saleFactory);

        $this->manager = $manager;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Transforms a cart to an order.
     *
     * @param CartInterface $cart
     *
     * @return OrderInterface
     */
    public function transformCartToOrder(CartInterface $cart)
    {
        $order = $this->orderRepository->createNew();

        $this->copySale($cart, $order);

        // TODO dispatch OrderEvents::PRE_CREATE / CartEvents::PRE_REMOVE ?

        $this->disableListeners();

        $this->manager->persist($order);
        $this->manager->flush();

        $this->enableListeners();

        // TODO dispatch OrderEvents::POST_CREATE / CartEvents::POST_REMOVE ?

        return $order;
    }

    /**
     * Disables some problematic listeners :-°
     */
    protected function disableListeners()
    {
        $eventManager = $this->manager->getEventManager();

        foreach ($eventManager->getListeners() as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof UploadableListener) {
                    $listener->setEnabled(false);
                }
            }
        }
    }

    /**
     * Enables some problematic listeners :-°
     */
    protected function enableListeners()
    {
        $eventManager = $this->manager->getEventManager();

        foreach ($eventManager->getListeners() as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof UploadableListener) {
                    $listener->setEnabled(true);
                }
            }
        }
    }
}
