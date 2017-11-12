<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Transformer\SaleTransformer as BaseTransformer;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;

/**
 * Class SaleTransformer
 * @package Ekyna\Bundle\CommerceBundle\Service\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleTransformer extends BaseTransformer
{
    /**
     * @inheritDoc
     */
    public function postCopy(SaleInterface $source, SaleInterface $target)
    {
        parent::postCopy($source, $target);

        // Order specific
        if ($target instanceof OrderInterface) {
            // If target sale has no origin customer
            if (null === $target->getOriginCustomer()) {
                // If source sale has customer
                if (null !== $customer = $source->getCustomer()) {
                    // If the source sale's origin customer is different from the target sale's customer
                    if ($customer !== $target->getCustomer()) {
                        // Set origin customer
                        $target->setOriginCustomer($customer);
                    }
                }
            }
        }
    }

//    /**
//     * Transforms a cart to an order.
//     *
//     * @param CartInterface $cart
//     *
//     * @return OrderInterface
//     */
//    public function transformCartToOrder(CartInterface $cart)
//    {
//        $order = $this->orderRepository->createNew();
//
//        $doProviderClear = $this->cartProvider->hasCart() && $this->cartProvider->getCart() === $cart;
//
//        $this->copySale($cart, $order);
//
//        $this->uploadableListener->setEnabled(false);
//
//        // Order PRE CREATE event
//        $orderEvent = $this->dispatcher->createResourceEvent($order);
//        $this->dispatcher->dispatch(OrderEvents::PRE_CREATE, $orderEvent);
//
//        // TODO (?) Cart PRE DELETE event
//        //$cartEvent = $this->dispatcher->createResourceEvent($cart);
//        //$this->dispatcher->dispatch(OrderEvents::PRE_DELETE, $cartEvent);
//
//        $this->manager->persist($order);
//        if ($doProviderClear) {
//            $this->cartProvider->clearCart(); // It calls EntityManager::flush()
//        } else {
//            $this->manager->remove($cart);
//            $this->manager->flush();
//        }
//
//        $this->uploadableListener->setEnabled(true);
//
//        // TODO dispatch OrderEvents::POST_CREATE / CartEvents::POST_REMOVE ?
//
//        return $order;
//    }
}
