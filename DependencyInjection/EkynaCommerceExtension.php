<?php

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection;

use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentHelper;
use Ekyna\Bundle\CommerceBundle\Service\Document\DocumentPageBuilder;
use Ekyna\Bundle\CommerceBundle\Service\Document\PdfGenerator;
use Ekyna\Bundle\CommerceBundle\Service\Document\RendererFactory;
use Ekyna\Bundle\CommerceBundle\Service\Widget\WidgetHelper;
use Ekyna\Bundle\CommerceBundle\Service\Widget\WidgetRenderer;
use Ekyna\Bundle\ResourceBundle\DependencyInjection\AbstractExtension;
use Ekyna\Component\Commerce\Bridge\Doctrine\DependencyInjection\DoctrineBundleMapping;
use Ekyna\Component\Commerce\Bridge\Mailchimp;
use Ekyna\Component\Commerce\Bridge\SendInBlue;
use Ekyna\Component\Commerce\Cart;
use Ekyna\Component\Commerce\Customer;
use Ekyna\Component\Commerce\Features;
use Ekyna\Component\Commerce\Order;
use Ekyna\Component\Commerce\Pricing\Api;
use Ekyna\Component\Commerce\Quote;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * Class EkynaCommerceExtension
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EkynaCommerceExtension extends AbstractExtension
{
    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->configure($configs, 'ekyna_commerce', new Configuration(), $container);

        $this->configureAccounting($config['accounting'], $container);
        $this->configureCache($config['cache'], $container);
        $this->configureDefaults($config['default'], $container);
        $this->configurePdf($config['pdf'], $container);
        $this->configureDocument($config['document'], $container);
        $this->configureFeatures($config['feature'], $container);
        $this->configurePricing($config['pricing'], $container);
        $this->configureStock($config['stock'], $container);
        $this->configureWidget($config['widget'], $container);
        $this->configureSaleFactory($container);

        if (in_array($container->getParameter('kernel.environment'), ['dev', 'test'], true)) {
            $this->loader->load('services_dev_test.xml');
        }
    }

    /**
     * Configures the defaults.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function configureDefaults(array $config, ContainerBuilder $container)
    {
        $container->setParameter('ekyna_commerce.default.company_logo', $config['company_logo']);
        $container->setParameter('ekyna_commerce.default.country', $config['country']);
        $container->setParameter('ekyna_commerce.default.currency', $config['currency']);
        $container->setParameter('ekyna_commerce.default.vat_display_mode', $config['vat_display_mode']);
        $container->setParameter('ekyna_commerce.default.fraud', $config['fraud']);
        $container->setParameter('ekyna_commerce.default.expiration.cart', $config['expiration']['cart']);
        $container->setParameter('ekyna_commerce.default.expiration.quote', $config['expiration']['quote']);
        $container->setParameter('ekyna_commerce.default.notify', $config['notify']);
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
            ->replaceArgument(8, $config);
    }

    /**
     * Configures PDF (generator).
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function configurePdf(array $config, ContainerBuilder $container)
    {
        $container
            ->getDefinition(PdfGenerator::class)
            ->setArguments([$config['entry_point'], $config['token']]);
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
            ->getDefinition(DocumentHelper::class)
            ->replaceArgument(6, array_replace([
                'logo_path' => '%ekyna_commerce.default.company_logo%',
            ], $config));

        $container
            ->getDefinition(DocumentPageBuilder::class)
            ->replaceArgument(2, $config);

        $container
            ->getDefinition(RendererFactory::class)
            ->replaceArgument(2, [
                'shipment_remaining_date' => $config['shipment_remaining_date'],
                'debug'                   => '%kernel.debug%',
            ]);
    }

    /**
     * Configures the features.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function configureFeatures(array $config, ContainerBuilder $container)
    {
        // Set features parameter
        $container->setParameter('ekyna_commerce.features', $config);
        // Set service config
        $container->getDefinition(Features::class)->replaceArgument(0, $config);

        if ($config[Features::NEWSLETTER]['enabled']) {
            $this->loader->load('services/newsletter.xml');

            $this->configureMailchimp($config[Features::NEWSLETTER]['mailchimp'], $container);
            $this->configureSendInBlue($config[Features::NEWSLETTER]['sendinblue'], $container);
        }
    }

    /**
     * Configures mailchimp newsletter gateway.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function configureMailchimp(array $config, ContainerBuilder $container): void
    {
        if (empty($config['api_key'])) {
            return;
        }

        if (!class_exists('DrewM\\MailChimp\\MailChimp')) {
            throw new LogicException(
                "To use MailChimp newsletter gateway, you must install drewm/mailchimp-api first.\n" .
                "Please run: composer require drewm/mailchimp-api"
            );
        }

        $this->loader->load('services/newsletter/mailchimp.xml');

        $container
            ->getDefinition(Mailchimp\Api::class)
            ->replaceArgument(1, $config['api_key']);
    }

    /**
     * Configures sendInBlue newsletter gateway.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function configureSendInBlue(array $config, ContainerBuilder $container): void
    {
        if (empty($config['api_key'])) {
            return;
        }

        if (!class_exists('SendinBlue\\Client\\Configuration')) {
            throw new LogicException(
                "To use SendInBlue newsletter gateway, you must install sendinblue/api-v3-sdk first.\n" .
                "Please run: composer require sendinblue/api-v3-sdk \"6.x.x\""
            );
        }

        $this->loader->load('services/newsletter/sendinblue.xml');

        $container
            ->getDefinition(SendInBlue\Api::class)
            ->replaceArgument(1, $config['api_key']);
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
     * Configures the sale factory classes.
     *
     * @param ContainerBuilder $container
     */
    private function configureSaleFactory(ContainerBuilder $container)
    {
        $factoryDefinition = $container->getDefinition('ekyna_commerce.sale_factory');

        $factoryDefinition->replaceArgument(0, [
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
     * Configures the stock.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function configureStock(array $config, ContainerBuilder $container)
    {
        $container->setParameter('ekyna_commerce.stock_subject_defaults', $config['subject_default']);

        $container
            ->getDefinition('ekyna_commerce.availability_helper')
            ->replaceArgument(2, $config['availability']['in_stock_limit']);
    }

    /**
     * Configures the widgets.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function configureWidget(array $config, ContainerBuilder $container)
    {
        $container
            ->getDefinition(WidgetHelper::class)
            ->replaceArgument(8, $config['data']);

        $container
            ->getDefinition(WidgetRenderer::class)
            ->replaceArgument(2, $config['template']);
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
                'mappings' => DoctrineBundleMapping::buildMappingConfiguration(),
            ],
        ]);
    }
}
