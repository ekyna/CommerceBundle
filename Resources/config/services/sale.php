<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\EventListener\SaleTransformListener;
use Ekyna\Bundle\CommerceBundle\Factory\AbstractSaleFactory;
use Ekyna\Component\Commerce\Bridge\Symfony\EventListener\SaleCopyListener;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Resolver\CartStateResolver;
use Ekyna\Component\Commerce\Common\Builder\AddressBuilder;
use Ekyna\Component\Commerce\Common\Builder\AdjustmentBuilder;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\MarginCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\WeightCalculator;
use Ekyna\Component\Commerce\Common\EventListener\AbstractAdjustmentListener;
use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleAddressListener;
use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleItemListener;
use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleListener;
use Ekyna\Component\Commerce\Common\EventListener\SaleDiscountListener;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelper;
use Ekyna\Component\Commerce\Common\Preparer\SalePreparer;
use Ekyna\Component\Commerce\Common\Resolver\DiscountResolver;
use Ekyna\Component\Commerce\Common\Resolver\SaleStateResolverFactory;
use Ekyna\Component\Commerce\Common\Transformer\SaleCopierFactory;
use Ekyna\Component\Commerce\Common\Transformer\SaleTransformer;
use Ekyna\Component\Commerce\Common\Updater\SaleUpdater;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Resolver\OrderStateResolver;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Commerce\Quote\Resolver\QuoteStateResolver;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Sale factory helper
        ->set('ekyna_commerce.helper.factory', FactoryHelper::class)
            ->args([
                service('ekyna_resource.factory.factory'),
            ])

        // Abstract sale factory
        ->set('ekyna_commerce.factory.abstract_sale', AbstractSaleFactory::class)
            ->abstract(true)
            ->args([
                service('ekyna_commerce.helper.factory'),
                service('ekyna_commerce.updater.sale'),
                service('ekyna_resource.provider.locale'),
                service('ekyna_commerce.provider.currency'),
                service('request_stack'),
                service('ekyna_commerce.repository.customer'),
            ])

        // Sale copier factory
        ->set('ekyna_commerce.factory.sale_copier', SaleCopierFactory::class)
            ->args([
                service('ekyna_commerce.helper.factory'),
            ])

        // Sale transformer
        ->set('ekyna_commerce.transformer.sale', SaleTransformer::class)
            ->lazy(true)
            ->args([
                service('ekyna_commerce.factory.sale_copier'),
                service('ekyna_resource.factory.factory'),
                service('ekyna_resource.manager.factory'),
                service('ekyna_resource.upload_toggler'),
                service('event_dispatcher'),
            ])

        // Sale copy event listener
        ->set('ekyna_commerce.listener.sale_copy', SaleCopyListener::class)
            ->tag('kernel.event_subscriber')

        // Sale transform event listener
        ->set('ekyna_commerce.listener.sale_transform', SaleTransformListener::class)
            ->lazy(true)
            ->args([
                service('ekyna_commerce.generator.order_number'),
                service('ekyna_commerce.generator.quote_number'),
                service('ekyna_commerce.generator.document'),
                service('doctrine.orm.default_entity_manager'),
            ])
            ->tag('kernel.event_subscriber')

        // Sale Updater
        ->set('ekyna_commerce.updater.sale', SaleUpdater::class)
            ->lazy(true)
            ->args([
                service('ekyna_commerce.builder.address'),
                service('ekyna_commerce.builder.adjustment'),
                service('ekyna_commerce.factory.amount_calculator'),
                service('ekyna_commerce.converter.currency'),
                service('ekyna_commerce.calculator.weight'),
                service('ekyna_commerce.resolver.shipment_price'),
                service('ekyna_commerce.calculator.payment'),
                service('ekyna_commerce.calculator.invoice_subject'),
                service('ekyna_commerce.releaser.outstanding'),
                service('ekyna_commerce.helper.factory'),
            ])

        // Sale preparer
        ->set('ekyna_commerce.preparer.sale', SalePreparer::class)
            ->lazy(true)
            ->args([
                service('ekyna_resource.event_dispatcher'),
                service('ekyna_commerce.prioritizer.stock'),
                service('ekyna_commerce.builder.shipment'),
                service('ekyna_commerce.helper.factory'),
            ])

        // Discount resolver
        ->set('ekyna_commerce.resolver.discount', DiscountResolver::class)
            ->args([
                service('event_dispatcher'),
            ])

        // Address builder
        ->set('ekyna_commerce.builder.address', AddressBuilder::class)
            ->lazy(true)
            ->args([
                service('ekyna_commerce.helper.factory'),
                service('ekyna_resource.orm.persistence_helper'),
            ])

        // Adjustment builder
        ->set('ekyna_commerce.builder.adjustment', AdjustmentBuilder::class)
            ->lazy(true)
            ->args([
                service('ekyna_commerce.helper.factory'),
                service('ekyna_commerce.resolver.tax'),
                service('ekyna_commerce.resolver.discount'),
                service('ekyna_resource.orm.persistence_helper'),
            ])

        // Weight calculator
        ->set('ekyna_commerce.calculator.weight', WeightCalculator::class)

        // Amount calculator factory
        ->set('ekyna_commerce.factory.amount_calculator', AmountCalculatorFactory::class)
            ->args([
                service('ekyna_commerce.converter.currency'),
                service('ekyna_commerce.calculator.invoice_subject'),
            ])

        // Margin calculator factory
        ->set('ekyna_commerce.factory.margin_calculator', MarginCalculatorFactory::class)
            ->lazy(true)
            ->args([
                service('ekyna_commerce.factory.amount_calculator'),
                service('ekyna_commerce.calculator.invoice_subject'),
                service('ekyna_commerce.converter.currency'),
                service('ekyna_commerce.helper.subject'),
                service('ekyna_commerce.guesser.subject_purchase_cost'),
                service('ekyna_commerce.resolver.shipment_address'),
                service('ekyna_commerce.calculator.shipment_weight'),
                service('ekyna_commerce.resolver.shipment_price'),
            ])

        // Sale state resolver factory
        ->set('ekyna_commerce.factory.sale_state_resolver', SaleStateResolverFactory::class)
            ->args([
                service('ekyna_commerce.resolver.state.payment_subject'),
                service('ekyna_commerce.resolver.state.shipment_subject'),
                service('ekyna_commerce.resolver.state.invoice_subject'),
                [
                    CartInterface::class  => CartStateResolver::class,
                    QuoteInterface::class => QuoteStateResolver::class,
                    OrderInterface::class => OrderStateResolver::class,
                ],
            ])

        // Sale discount listener
        ->set('ekyna_commerce.listener.sale_discount', SaleDiscountListener::class)
            ->args([
                service('ekyna_commerce.repository.coupon'),
            ])
            ->tag('kernel.event_subscriber')

        // Sale abstract listener
        ->set('ekyna_commerce.listener.abstract_sale', AbstractSaleListener::class)
            ->abstract(true)
            ->call('setPersistenceHelper', [service('ekyna_resource.orm.persistence_helper')])
            ->call('setKeyGenerator', [service('ekyna_commerce.generator.key')])
            ->call('setPricingUpdater', [service('ekyna_commerce.updater.pricing')])
            ->call('setFactoryHelper', [service('ekyna_commerce.helper.factory')])
            ->call('setSaleUpdater', [service('ekyna_commerce.updater.sale')])
            ->call('setDueDateResolver', [service('ekyna_commerce.resolver.due_date')])
            ->call('setCurrencyProvider', [service('ekyna_commerce.provider.currency')])
            ->call('setLocaleProvider', [service('ekyna_resource.provider.locale')])
            ->call('setAmountCalculatorFactory', [service('ekyna_commerce.factory.amount_calculator')])
            ->call('setDefaultVatDisplayMode', [param('ekyna_commerce.default.vat_display_mode')])

        // Sale address abstract listener
        ->set('ekyna_commerce.listener.abstract_sale_address', AbstractSaleAddressListener::class)
            ->abstract(true)
            ->call('setPersistenceHelper', [service('ekyna_resource.orm.persistence_helper')])

        // Sale item abstract listener
        ->set('ekyna_commerce.listener.abstract_sale_item', AbstractSaleItemListener::class)
            ->abstract(true)
            ->call('setPersistenceHelper', [service('ekyna_resource.orm.persistence_helper')])
            ->call('setAdjustmentBuilder', [service('ekyna_commerce.builder.adjustment')])

        // Sale adjustment abstract listener
        ->set('ekyna_commerce.listener.abstract_sale_adjustment', AbstractAdjustmentListener::class)
            ->abstract(true)
            ->call('setPersistenceHelper', [service('ekyna_resource.orm.persistence_helper')])
    ;
};
