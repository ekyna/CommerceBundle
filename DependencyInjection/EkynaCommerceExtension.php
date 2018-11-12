<?php

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection;

use Ekyna\Bundle\ResourceBundle\DependencyInjection\AbstractExtension;
use Ekyna\Component\Commerce\Bridge\Doctrine\DependencyInjection\DoctrineBundleMapping;
use Ekyna\Component\Commerce\Cart;
use Ekyna\Component\Commerce\Customer;
use Ekyna\Component\Commerce\Order;
use Ekyna\Component\Commerce\Quote;
use Ekyna\Component\Commerce\Pricing\Api;
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

        $this->configureAccounting($config['accounting'], $container);
        $this->configureCache($config['cache'], $container);
        $this->configureDefaults($config['default'], $container);
        $this->configureDocument($config['document'], $container);
        $this->configurePricing($config['pricing'], $container);
        $this->configureStock($config['stock'], $container);
        $this->configureSaleFactory($container);
    }

    /**
     * Configures the defaults.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function configureDefaults(array $config, ContainerBuilder $container)
    {
        // TODO 'ekyna_commerce.default.*' for all
        $container->setParameter('ekyna_commerce.company_logo', $config['company_logo']);
        $container->setParameter('ekyna_commerce.default.country', $config['country']);
        $container->setParameter('ekyna_commerce.default.currency', $config['currency']);
        $container->setParameter('ekyna_commerce.default.vat_display_mode', $config['vat_display_mode']);
        $container->setParameter('ekyna_commerce.default.customer', $config['customer']);
        $container->setParameter('ekyna_commerce.default.fraud', $config['fraud']);

        $container->setParameter('ekyna_commerce.expiration.cart', $config['expiration']['cart']);
        $container->setParameter('ekyna_commerce.expiration.quote', $config['expiration']['quote']);
    }

    /**
     * Configures the cache.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function configureCache(array $config, ContainerBuilder $container)
    {
        $container->setParameter('ekyna_commerce.cache.countries', $config['countries']);
    }

    /**
     * Configures accounting.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function configureAccounting(array $config, ContainerBuilder $container)
    {
        $container
            ->getDefinition('ekyna_commerce.accounting.exporter')
            ->replaceArgument(5, $config);
    }

    /**
     * Configures document.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function configureDocument(array $config, ContainerBuilder $container)
    {
        $container
            ->getDefinition('ekyna_commerce.document.page_builder')
            ->replaceArgument(2, $config);
    }

    /**
     * Configures pricing.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function configurePricing(array $config, ContainerBuilder $container)
    {
        $configs = $config['provider'];

        // Register Europa Api provider service if enabled
        if ($configs['europa']['enabled']) {
            $container
                ->register(Api\Provider\Europa::SERVICE_ID, Api\Provider\Europa::class)
                ->addTag(Api\PricingApi::PROVIDER_TAG)
                ->addArgument('%kernel.debug%');
        }

        // register VatLayer Api provider service if enabled and access key is set
        if ($configs['vat_layer']['enabled'] && !empty($configs['vat_layer']['access_key'])) {
            $container
                ->register(Api\Provider\VatLayer::SERVICE_ID, Api\Provider\VatLayer::class)
                ->addTag(Api\PricingApi::PROVIDER_TAG)
                ->addArgument($configs['vat_layer']['access_key'])
                ->addArgument('%kernel.debug%');
        }

        // Build VatApi Api provider service if enabled and access key is set
        if ($configs['vat_api']['enabled'] && !empty($configs['vat_api']['access_key'])) {
            $container
                ->register(Api\Provider\VatApi::SERVICE_ID, Api\Provider\VatApi::class)
                ->addTag(Api\PricingApi::PROVIDER_TAG)
                ->addArgument($configs['vat_api']['access_key'])
                ->addArgument('%kernel.debug%');
        }
    }

    /**
     * Configures the stock.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function configureStock(array $config, ContainerBuilder $container)
    {
        $availability = $config['availability'];

        $container
            ->getDefinition('ekyna_commerce.availability_helper')
            ->replaceArgument(2, $availability['in_stock_limit']);
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
            'adjustment'      => [
                Cart\Model\CartInterface::class   => '%ekyna_commerce.cart_adjustment.class%',
                Order\Model\OrderInterface::class => '%ekyna_commerce.order_adjustment.class%',
                Quote\Model\QuoteInterface::class => '%ekyna_commerce.quote_adjustment.class%',
            ],
            'invoice'         => [
                Order\Model\OrderInterface::class => '%ekyna_commerce.order_invoice.class%',
            ],
            'invoice_line'    => [
                Order\Model\OrderInvoiceInterface::class => '%ekyna_commerce.order_invoice_line.class%',
            ],
            'item'            => [
                Cart\Model\CartInterface::class   => '%ekyna_commerce.cart_item.class%',
                Order\Model\OrderInterface::class => '%ekyna_commerce.order_item.class%',
                Quote\Model\QuoteInterface::class => '%ekyna_commerce.quote_item.class%',
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
            'shipment_item'   => [
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
            'dbal' => [
                'types' => DoctrineBundleMapping::buildTypesConfiguration(),
            ],
            'orm'  => [
                'mappings' => [
                    'EkynaCommerce' => DoctrineBundleMapping::buildMappingConfiguration(),
                ],
            ],
        ]);
    }
}
