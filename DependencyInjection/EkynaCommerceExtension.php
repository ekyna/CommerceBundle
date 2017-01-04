<?php

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection;

use Ekyna\Bundle\ResourceBundle\DependencyInjection\AbstractExtension;
use Ekyna\Component\Commerce\Bridge\Doctrine\DependencyInjection\DoctrineBundleMapping;
use Ekyna\Component\Commerce\Cart;
use Ekyna\Component\Commerce\Customer;
use Ekyna\Component\Commerce\Order;
use Ekyna\Component\Commerce\Quote;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EkynaCommerceExtension
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EkynaCommerceExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->configure($configs, 'ekyna_commerce', new Configuration(), $container);

        $container->setParameter('ekyna_commerce.default.countries', $config['default']['countries']);
        $container->setParameter('ekyna_commerce.default.currencies', $config['default']['currencies']);

        $this->configureSaleFactory($container);
    }

    /**
     * Configures the sale factory classes.
     *
     * @param ContainerBuilder $container
     */
    private function configureSaleFactory(ContainerBuilder $container)
    {
        $factoryDefinition = $container->getDefinition('ekyna_commerce.sale_factory');

        $factoryDefinition->replaceArgument(2, [
            'address'         => [
                Cart\Model\CartInterface::class   => '%ekyna_commerce.cart_address.class%',
                Order\Model\OrderInterface::class => '%ekyna_commerce.order_address.class%',
                Quote\Model\QuoteInterface::class => '%ekyna_commerce.quote_address.class%',
            ],
            'item'            => [
                Cart\Model\CartInterface::class   => '%ekyna_commerce.cart_item.class%',
                Order\Model\OrderInterface::class => '%ekyna_commerce.order_item.class%',
                Quote\Model\QuoteInterface::class => '%ekyna_commerce.quote_item.class%',
            ],
            'adjustment'      => [
                Cart\Model\CartInterface::class   => '%ekyna_commerce.cart_adjustment.class%',
                Order\Model\OrderInterface::class => '%ekyna_commerce.order_adjustment.class%',
                Quote\Model\QuoteInterface::class => '%ekyna_commerce.quote_adjustment.class%',
            ],
            'item_adjustment' => [
                Cart\Model\CartItemInterface::class   => '%ekyna_commerce.cart_item_adjustment.class%',
                Order\Model\OrderItemInterface::class => '%ekyna_commerce.order_item_adjustment.class%',
                Quote\Model\QuoteItemInterface::class => '%ekyna_commerce.quote_item_adjustment.class%',
            ],
            'payment'         => [
                Cart\Model\CartInterface::class   => '%ekyna_commerce.cart_payment.class%',
                Order\Model\OrderInterface::class => '%ekyna_commerce.order_payment.class%',
                Quote\Model\QuoteInterface::class => '%ekyna_commerce.quote_payment.class%',
            ],
            'shipment'        => [
                Order\Model\OrderInterface::class => '%ekyna_commerce.order_shipment.class%',
            ],
            'shipment_item'        => [
                Order\Model\OrderShipmentInterface::class => '%ekyna_commerce.order_shipment_item.class%',
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        parent::prepend($container);

        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'mappings' => [
                    'EkynaCommerce' => DoctrineBundleMapping::buildMappingConfiguration(),
                ],
            ],
        ]);
    }
}
