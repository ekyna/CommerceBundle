<?php

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection;

use Ekyna\Bundle\ResourceBundle\DependencyInjection\AbstractExtension;
use Ekyna\Component\Commerce\Bridge\Doctrine\DependencyInjection\DoctrineBundleMapping;
use Ekyna\Component\Commerce\Cart;
use Ekyna\Component\Commerce\Customer;
use Ekyna\Component\Commerce\Order;
use Ekyna\Component\Commerce\Quote;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

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

        $container->setParameter('ekyna_commerce.company_logo', $config['default']['company_logo']);
        $container->setParameter('ekyna_commerce.default.countries', $config['default']['countries']);
        $container->setParameter('ekyna_commerce.default.currencies', $config['default']['currencies']);

        $this->configureVatValidator($config['vat_validator'], $container);
        $this->configureSaleFactory($container);
    }

    /**
     * Configures the VAT validator.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function configureVatValidator(array $config, ContainerBuilder $container)
    {
        $references = [];

        // Build Vat Layer provider if enabled
        if ($config['vat_layer']['enabled'] && !empty($config['vat_layer']['access_key'])) {
            $container
                ->register(
                    Customer\Validator\Provider\VatLayer::SERVICE_ID,
                    Customer\Validator\Provider\VatLayer::class
                )
                ->addArgument($config['vat_layer']['access_key'])
                ->addArgument('%kernel.debug%');

            $references[] = new Reference(Customer\Validator\Provider\VatLayer::SERVICE_ID);
        }

        // Build Vat Api provider if enabled
        if ($config['vat_api']['enabled'] && !empty($config['vat_api']['access_key'])) {
            $container
                ->register(
                    Customer\Validator\Provider\VatApi::SERVICE_ID,
                    Customer\Validator\Provider\VatApi::class
                )
                ->addArgument($config['vat_api']['access_key'])
                ->addArgument('%kernel.debug%');

            $references[] = new Reference(Customer\Validator\Provider\VatApi::SERVICE_ID);
        }

        // Build Europa provider if enabled
        if ($config['europa']['enabled']) {
            $container
                ->register(
                    Customer\Validator\Provider\Europa::SERVICE_ID,
                    Customer\Validator\Provider\Europa::class
                )
                ->addArgument('%kernel.debug%');

            $references[] = new Reference(Customer\Validator\Provider\Europa::SERVICE_ID);
        }

        // Add providers to the validator service
        $container
            ->getDefinition('ekyna_commerce.customer.validator.vat_number')
            ->replaceArgument(0, $references);
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
            'orm' => [
                'mappings' => [
                    'EkynaCommerce' => DoctrineBundleMapping::buildMappingConfiguration(),
                ],
            ],
        ]);
    }
}
