<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection;

use Ekyna\Bundle\CommerceBundle\EventListener\AddressEventSubscriber;
use Ekyna\Bundle\ResourceBundle\DependencyInjection\PrependBundleConfigTrait;
use Ekyna\Component\Commerce\Bridge\Doctrine\DependencyInjection\DoctrineBundleMapping;
use Ekyna\Component\Commerce\Cart;
use Ekyna\Component\Commerce\Customer;
use Ekyna\Component\Commerce\Features;
use Ekyna\Component\Commerce\Order;
use Ekyna\Component\Commerce\Pricing\Api;
use Ekyna\Component\Commerce\Quote;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

use function array_replace;
use function class_exists;
use function in_array;

/**
 * Class EkynaCommerceExtension
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EkynaCommerceExtension extends Extension implements PrependExtensionInterface
{
    use PrependBundleConfigTrait;

    public function prepend(ContainerBuilder $container): void
    {
        $this->prependBundleConfigFiles($container);

        $container->prependExtensionConfig('doctrine', [
            'dbal' => [
                'types' => DoctrineBundleMapping::buildTypesConfiguration(),
            ],
            'orm'  => [
                'mappings' => DoctrineBundleMapping::buildMappingConfiguration(),
            ],
        ]);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $this->configureParameters($config, $container);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services/accounting.php');
        $loader->load('services/actions.php');
        $loader->load('services/cart.php');
        $loader->load('services/command.php');
        $loader->load('services/common.php');
        $loader->load('services/controller.php');
        $loader->load('services/customer.php');
        $loader->load('services/document.php');
        $loader->load('services/form.php');
        $loader->load('services/helper.php');
        $loader->load('services/invoice.php');
        $loader->load('services/loyalty.php');
        $loader->load('services/map.php');
        $loader->load('services/notify.php');
        $loader->load('services/order.php');
        $loader->load('services/payment.php');
        $loader->load('services/pricing.php');
        $loader->load('services/quote.php');
        $loader->load('services/report.php');
        $loader->load('services/sale.php');
        $loader->load('services/serializer.php');
        $loader->load('services/shipment.php');
        $loader->load('services/show.php');
        $loader->load('services/stat.php');
        $loader->load('services/stock.php');
        $loader->load('services/subject.php');
        $loader->load('services/supplier.php');
        $loader->load('services/support.php'); // TODO Regarding enabled features
        $loader->load('services/table.php');
        $loader->load('services/twig.php');
        $loader->load('services/validator.php');
        $loader->load('services/view.php');
        $loader->load('services.php');

        if (in_array($container->getParameter('kernel.environment'), ['dev', 'test'], true)) {
            $loader->load('services/dev.php');
        }

        $this->configureAccounting($config['accounting'], $container);
        $this->configureCache($config['cache'], $container);
        $this->configureDocument($config['document'], $container);
        $this->configureFeatures($config['feature'], $container, $loader);
        $this->configureGoogle($container);
        $this->configureNotify($config['default'], $container);
        $this->configureLocking($config['default'], $container);
        $this->configurePricing($config['pricing'], $container);
        $this->configureShipment($config['default'], $container);
        $this->configureStock($config['stock'], $container);
        $this->configureSubject($config['subject'], $container);
        $this->configureTemplates($config['template'], $container);
        $this->configureWidget($config['widget'], $container);
    }

    private function configureParameters(array $config, ContainerBuilder $container): void
    {
        $default = $config['default'];
        $container->setParameter('ekyna_commerce.default.company_logo', $default['company_logo']);
        $container->setParameter('ekyna_commerce.default.country', $default['country']);
        $container->setParameter('ekyna_commerce.default.currency', $default['currency']);
        $container->setParameter('ekyna_commerce.default.vat_display_mode', $default['vat_display_mode']);
        $container->setParameter('ekyna_commerce.default.fraud', $default['fraud']);
        $container->setParameter('ekyna_commerce.default.expiration.cart', $default['expiration']['cart']);
        $container->setParameter('ekyna_commerce.default.expiration.quote', $default['expiration']['quote']);

        $classes = $config['class'];
        $container->setParameter('ekyna_commerce.class.context', $classes['context']);
        $container->setParameter('ekyna_commerce.class.genders', $classes['genders']);
    }

    private function configureLocking(array $config, ContainerBuilder $container): void
    {
        $container
            ->getDefinition('ekyna_commerce.checker.locking')
            ->replaceArgument(1, $config['locking']['start'])
            ->replaceArgument(2, $config['locking']['end'])
            ->replaceArgument(3, $config['locking']['since']);
    }

    private function configureCache(array $config, ContainerBuilder $container): void
    {
        $container->setParameter('ekyna_commerce.cache.countries', $config['countries']);
    }

    private function configureAccounting(array $config, ContainerBuilder $container): void
    {
        $container
            ->getDefinition('ekyna_commerce.exporter.accounting')
            ->replaceArgument(8, $config);
    }

    private function configureDocument(array $config, ContainerBuilder $container): void
    {
        $container
            ->getDefinition('ekyna_commerce.helper.document')
            ->replaceArgument(6, array_replace([
                'logo_path' => '%ekyna_commerce.default.company_logo%',
            ], $config));

        $container
            ->getDefinition('ekyna_commerce.builder.document_page')
            ->replaceArgument(2, $config);

        $container
            ->getDefinition('ekyna_commerce.factory.document_renderer')
            ->replaceArgument(2, [
                'shipment_remaining_date' => $config['shipment_remaining_date'],
                'debug'                   => '%kernel.debug%',
            ]);
    }

    private function configureFeatures(array $config, ContainerBuilder $container, PhpFileLoader $loader): void
    {
        // Set features parameter
        $container->setParameter('ekyna_commerce.features', $config);
        // Set service config
        $container->getDefinition('ekyna_commerce.features')->replaceArgument(0, $config);

        if (!$config[Features::NEWSLETTER]['enabled']) {
            return;
        }

        $loader->load('services/newsletter.php');

        $this->configureMailchimp($config[Features::NEWSLETTER]['mailchimp'], $container, $loader);
        $this->configureSendInBlue($config[Features::NEWSLETTER]['sendinblue'], $container, $loader);
    }

    private function configureGoogle(ContainerBuilder $container): void
    {
        if (!$container->has('ivory.google_map.geocoder')) {
            return;
        }

        // Address event listener (geocoding)
        $container
            ->register('ekyna_commerce.listener.address', AddressEventSubscriber::class)
            ->setArguments([
                new Reference('ekyna_resource.orm.persistence_helper'),
                new Reference('ivory.google_map.geocoder'),
            ])
            ->addTag('resource.event_subscriber');
    }

    private function configureNotify(array $config, ContainerBuilder $container): void
    {
        $container
            ->getDefinition('ekyna_commerce.helper.notify')
            ->replaceArgument(3, $config['notify']);
    }

    private function configureMailchimp(array $config, ContainerBuilder $container, PhpFileLoader $loader): void
    {
        if (empty($config['api_key'])) {
            return;
        }

        if (!class_exists('DrewM\\MailChimp\\MailChimp')) {
            throw new LogicException(
                'To use MailChimp newsletter gateway, you must install drewm/mailchimp-api first. ' .
                'Please run: composer require drewm/mailchimp-api:^2.5'
            );
        }

        $loader->load('services/newsletter/mailchimp.php');

        $container
            ->getDefinition('ekyna_commerce.newsletter.api.mailchimp')
            ->replaceArgument(1, $config['api_key']);
    }

    private function configureSendInBlue(array $config, ContainerBuilder $container, PhpFileLoader $loader): void
    {
        if (empty($config['api_key'])) {
            return;
        }

        if (!class_exists('SendinBlue\\Client\\Configuration')) {
            throw new LogicException(
                'To use SendInBlue newsletter gateway, you must install sendinblue/api-v3-sdk first. ' .
                'Please run: composer require sendinblue/api-v3-sdk:^6.4'
            );
        }

        $loader->load('services/newsletter/sendinblue.php');

        $container
            ->getDefinition('ekyna_commerce.newsletter.api.sendinblue')
            ->replaceArgument(1, $config['api_key']);
    }

    private function configurePricing(array $config, ContainerBuilder $container): void
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

    private function configureShipment(array $config, ContainerBuilder $container): void
    {
        $container
            ->getDefinition('ekyna_commerce.command.shipment_label_purge')
            ->replaceArgument(1, $config['shipment']['label_retention']);
    }

    private function configureStock(array $config, ContainerBuilder $container): void
    {
        $container
            ->getDefinition('ekyna_commerce.updater.stock_subject')
            ->replaceArgument(2, $config['subject_default']);

        $container
            ->getDefinition('ekyna_commerce.helper.availability')
            ->replaceArgument(2, $config['availability']['in_stock_limit']);
    }

    private function configureSubject(array $config, ContainerBuilder $container): void
    {
        $container
            ->getDefinition('ekyna_commerce.helper.subject')
            ->addMethodCall('setConfig', [$config]);
    }

    private function configureTemplates(array $config, ContainerBuilder $container): void
    {
        $container
            ->getDefinition('ekyna_commerce.renderer.stock')
            ->replaceArgument(2, $config['stock_unit_list'])
            ->replaceArgument(3, $config['stock_assignment_list'])
            ->replaceArgument(4, $config['subject_stock_list']);

        $container
            ->getDefinition('ekyna_commerce.renderer.shipment')
            ->replaceArgument(3, $config['shipment_price_list']);
    }

    private function configureWidget(array $config, ContainerBuilder $container): void
    {
        $container
            ->getDefinition('ekyna_commerce.helper.widget')
            ->replaceArgument(8, $config['data']);

        $container
            ->getDefinition('ekyna_commerce.renderer.widget')
            ->replaceArgument(2, $config['template']);
    }
}
