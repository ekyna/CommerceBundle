<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Common;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Provider\CartProviderInterface;
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
     * @var CartProviderInterface
     */
    private $cartProvider;

    /**
     * @var UploadableListener
     */
    private $uploadableListener;


    /**
     * Constructor.
     *
     * @param SaleFactoryInterface     $saleFactory
     * @param EntityManagerInterface   $manager
     * @param OrderRepositoryInterface $orderRepository
     * @param CartProviderInterface    $cartProvider
     * @param UploadableListener       $uploadableListener
     */
    public function __construct(
        SaleFactoryInterface $saleFactory,
        EntityManagerInterface $manager,
        OrderRepositoryInterface $orderRepository,
        CartProviderInterface $cartProvider,
        UploadableListener $uploadableListener
    ) {
        parent::__construct($saleFactory);

        $this->manager = $manager;
        $this->orderRepository = $orderRepository;
        $this->cartProvider = $cartProvider;
        $this->uploadableListener = $uploadableListener;
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

        $doProviderClear = $this->cartProvider->hasCart() && $this->cartProvider->getCart() === $cart;

        $this->copySale($cart, $order);

        // TODO dispatch OrderEvents::PRE_CREATE / CartEvents::PRE_REMOVE ?

        $this->uploadableListener->setEnabled(false);

        $this->manager->persist($order);
        if ($doProviderClear) {
            $this->cartProvider->clearCart(); // It calls EntityManager::flush()
        } else {
            $this->manager->flush();
        }

        $this->uploadableListener->setEnabled(true);

        // TODO dispatch OrderEvents::POST_CREATE / CartEvents::POST_REMOVE ?

        return $order;
    }
}
