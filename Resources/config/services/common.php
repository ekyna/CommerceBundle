<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\Events;
use Ekyna\Bundle\CommerceBundle\EventListener\ContextEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\SaleItemEventSubscriber;
use Ekyna\Bundle\CommerceBundle\EventListener\SaleOperationListener;
use Ekyna\Bundle\CommerceBundle\Service\Common\ButtonRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Common\CommonRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Common\CouponHelper;
use Ekyna\Bundle\CommerceBundle\Service\Common\CustomerRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Common\FlagRenderer;
use Ekyna\Bundle\CommerceBundle\Service\Common\InChargeResolver;
use Ekyna\Bundle\CommerceBundle\Service\Common\KeyGenerator;
use Ekyna\Bundle\CommerceBundle\Service\Country\SessionCountryProvider;
use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Provider\DoctrineProvider;
use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\CountryRepository;
use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\CurrencyRepository;
use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\CustomerGroupRepository;
use Ekyna\Component\Commerce\Bridge\Swap\SwapProvider;
use Ekyna\Component\Commerce\Bridge\Symfony\Currency\CachedExchangeRateProvider;
use Ekyna\Component\Commerce\Bridge\Symfony\Currency\SessionCurrencyProvider;
use Ekyna\Component\Commerce\Bridge\Symfony\Transformer\ArrayToAddressTransformer;
use Ekyna\Component\Commerce\Common\Context\ContextProvider;
use Ekyna\Component\Commerce\Common\Currency\ArrayExchangeRateProvider;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverter;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Currency\CurrencyRenderer;
use Ekyna\Component\Commerce\Common\Currency\CurrencyRendererInterface;
use Ekyna\Component\Commerce\Common\Currency\ExchangeRateProviderInterface;
use Ekyna\Component\Commerce\Common\Event\SaleTransformEvents;
use Ekyna\Component\Commerce\Common\Export\RegionProvider;
use Ekyna\Component\Commerce\Common\Export\SaleCsvExporter;
use Ekyna\Component\Commerce\Common\Export\SaleXlsExporter;
use Ekyna\Component\Commerce\Common\Locking\LockChecker;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoiceLockResolver;
use Ekyna\Component\Commerce\Order\Resolver\OrderPaymentLockResolver;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Array address transformer
    $services
        ->set('ekyna_commerce.transformer.array_address', ArrayToAddressTransformer::class)
        ->args([
            service('ekyna_commerce.repository.country'),
            service('libphonenumber\PhoneNumberUtil'),
        ]);

    // Key generator
    $services
        ->set('ekyna_commerce.generator.key', KeyGenerator::class)
        ->args([
            service('doctrine'),
        ]);

    // Region provider
    $services->set('ekyna_commerce.provider.region', RegionProvider::class);

    // Country repository
    $services
        ->set('ekyna_commerce.repository.country', CountryRepository::class)
        ->call('setDefaultCode', [param('ekyna_commerce.default.country')])
        ->call('setCachedCodes', [param('ekyna_commerce.cache.countries')])
        ->tag('doctrine.event_listener', [
            'event'      => Events::onClear,
            'connection' => 'default',
        ]);

    // Currency repository
    $services
        ->set('ekyna_commerce.repository.currency', CurrencyRepository::class)
        ->call('setDefaultCode', [param('ekyna_commerce.default.currency')])
        ->tag('doctrine.event_listener', [
            'event'      => Events::onClear,
            'connection' => 'default',
        ]);

    // Customer group repository
    $services
        ->set('ekyna_commerce.repository.customer_group', CustomerGroupRepository::class)
        ->tag('doctrine.event_listener', [
            'event'      => Events::onClear,
            'connection' => 'default',
        ]);

    // Country provider
    $services
        ->set('ekyna_commerce.provider.country', SessionCountryProvider::class)
        ->lazy()
        ->args([
            service('ekyna_commerce.repository.country'),
            service('request_stack'),
            param('ekyna_commerce.default.country'),
        ])
        ->call('setCountryGuesser', [service('ekyna_ui.geo.user_country_guesser')]);

    // Currency provider
    $services
        ->set('ekyna_commerce.provider.currency', SessionCurrencyProvider::class)
        ->lazy()
        ->args([
            service('ekyna_commerce.repository.currency'),
            service('request_stack'),
            param('ekyna_commerce.default.currency'),
        ]);

    // Context provider
    $services
        ->set('ekyna_commerce.provider.context', ContextProvider::class)
        ->args([
            service('event_dispatcher'),
            service('ekyna_commerce.provider.cart'),
            service('ekyna_commerce.provider.customer'),
            service('ekyna_resource.provider.locale'),
            service('ekyna_commerce.provider.currency'),
            service('ekyna_commerce.provider.country'),
            service('ekyna_commerce.provider.warehouse'),
            service('ekyna_commerce.repository.customer_group'),
            param('ekyna_commerce.default.vat_display_mode'),
            param('ekyna_commerce.class.context'),
        ])
        ->tag('doctrine.event_listener', [
            'event'      => Events::onClear,
            'connection' => 'default',
        ]);

    // Context event listener
    $services
        ->set('ekyna_commerce.listener.context', ContextEventSubscriber::class)
        ->args([
            service('ekyna_commerce.provider.cart'),
            service('ekyna_commerce.updater.sale'),
            service('security.token_storage'),
            service('security.authorization_checker'),
        ])
        ->tag('kernel.event_subscriber');

    // Swap exchange rate provider
    $services
        ->set('ekyna_commerce.provider.exchange_rate.swap', SwapProvider::class)
        ->args([
            service('florianv_swap.swap'),
        ]);

    // Doctrine exchange rate provider
    $services
        ->set('ekyna_commerce.provider.exchange_rate.doctrine', DoctrineProvider::class)
        ->args([
            service('doctrine.dbal.default_connection'),
            service('ekyna_commerce.provider.exchange_rate.swap'),
        ]);

    // Cache exchange rate provider
    $services
        ->set('ekyna_commerce.provider.exchange_rate.cached', CachedExchangeRateProvider::class)
        ->args([
            service('ekyna_commerce.cache'),
            service('ekyna_commerce.provider.exchange_rate.doctrine'),
        ]);

    // Array exchange rate provider
    $services
        ->set('ekyna_commerce.provider.exchange_rate', ArrayExchangeRateProvider::class)
        ->args([
            [], // Empty rate list
            service('ekyna_commerce.provider.exchange_rate.cached'),
        ])
        ->alias(ExchangeRateProviderInterface::class, 'ekyna_commerce.provider.exchange_rate');

    // Currency converter
    $services
        ->set('ekyna_commerce.converter.currency', CurrencyConverter::class)
        ->args([
            service('ekyna_commerce.provider.exchange_rate'),
            param('ekyna_commerce.default.currency'),
        ])
        ->set(CurrencyConverterInterface::class, 'ekyna_commerce.converter.currency');

    // Formatter factory
    $services
        ->set('ekyna_commerce.factory.formatter', FormatterFactory::class)
        ->args([
            service('ekyna_resource.provider.locale'),
            service('ekyna_commerce.provider.currency'),
        ]);

    // Coupon setter
    $services
        ->set('ekyna_commerce.helper.coupon', CouponHelper::class)
        ->args([
            service('ekyna_commerce.repository.coupon'),
            service('ekyna_commerce.factory.amount_calculator'),
            service('form.factory'),
            service('router'),
            service('translator'),
            param('ekyna_commerce.default.currency'),
        ])
        ->call('setFormatterFactory', [service('ekyna_commerce.factory.formatter')]);

    // In charge resolver
    $services
        ->set('ekyna_commerce.resolver.in_charge', InChargeResolver::class)
        ->args([
            service('ekyna_admin.provider.user'),
        ]);

    // Sale item event listener
    $services
        ->set('ekyna_commerce.listener.sale_item', SaleItemEventSubscriber::class)
        ->args([
            service('ekyna_commerce.provider.context'),
        ])
        ->tag('kernel.event_subscriber');

    // Sale transform listener
    $services
        ->set('ekyna_commerce.listener.sale_operation', SaleOperationListener::class)
        ->args([
            service('ekyna_commerce.checker.sale_items'),
        ])
        ->tag('kernel.event_listener', [
            'event'  => SaleTransformEvents::PRE_TRANSFORM,
            'method' => 'onPreTransform',
        ])
        ->tag('kernel.event_listener', [
            'event'  => SaleTransformEvents::PRE_DUPLICATE,
            'method' => 'onPreDuplicate',
        ]);

    // Common renderer
    // TODO Split
    $services
        ->set('ekyna_commerce.renderer.common', CommonRenderer::class)
        ->args([
            service('twig'),
            service('ekyna_commerce.transformer.array_address'),
        ])
        ->tag('twig.runtime');

    // Button renderer
    $services
        ->set('ekyna_commerce.renderer.button', ButtonRenderer::class)
        ->args([
            service('event_dispatcher'),
            service('ekyna_ui.renderer'),
        ])
        ->tag('twig.runtime');

    // Currency renderer
    $services
        ->set('ekyna_commerce.renderer.currency', CurrencyRenderer::class)
        ->args([
            service('ekyna_commerce.converter.currency'),
            service('ekyna_commerce.factory.formatter'),
        ])
        ->tag('twig.runtime')
        ->alias(CurrencyRendererInterface::class, 'ekyna_commerce.renderer.currency');

    // Customer renderer
    $services
        ->set('ekyna_commerce.renderer.customer', CustomerRenderer::class)
        ->args([
            service('ekyna_resource.repository.factory'),
            param('ekyna_commerce.default.country'),
        ])
        ->tag('twig.runtime');

    // Flag renderer
    $services
        ->set('ekyna_commerce.renderer.flag', FlagRenderer::class)
        ->args([
            service('translator'),
        ])
        ->tag('twig.runtime');

    // Sale CSV exporter
    $services
        ->set('ekyna_commerce.exporter.sale_csv', SaleCsvExporter::class)
        ->args([
            service('ekyna_commerce.builder.view'),
        ]);

    // Sale XLS exporter
    $services
        ->set('ekyna_commerce.exporter.sale_xls', SaleXlsExporter::class)
        ->args([
            service('ekyna_commerce.builder.view'),
            service('ekyna_commerce.renderer.common'),
            service('translator'),
        ]);

    // Lock checker
    $services
        ->set('ekyna_commerce.checker.locking', LockChecker::class)
        ->args([
            [
                inline_service(InvoiceLockResolver::class),
                inline_service(OrderPaymentLockResolver::class)->args([
                    service('ekyna_resource.orm.persistence_helper'),
                ]),
            ],
            abstract_arg('Lock start'),
            abstract_arg('Lock end'),
            abstract_arg('Lock since'),
        ])
        ->tag('twig.runtime');
};
