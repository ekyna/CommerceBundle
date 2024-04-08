<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Service\AbstractViewType;
use Ekyna\Bundle\CommerceBundle\Service\Cart\CartViewType;
use Ekyna\Bundle\CommerceBundle\Service\Common\SaleViewPrivacyType;
use Ekyna\Bundle\CommerceBundle\Service\Common\SaleViewType;
use Ekyna\Bundle\CommerceBundle\Service\Order\OrderViewType;
use Ekyna\Bundle\CommerceBundle\Service\Quote\QuoteAccountViewType;
use Ekyna\Bundle\CommerceBundle\Service\Quote\QuoteAdminViewType;
use Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\RegisterViewTypePass;
use Ekyna\Component\Commerce\Common\View\AvailabilityViewType;
use Ekyna\Component\Commerce\Common\View\ViewBuilder;
use Ekyna\Component\Commerce\Common\View\ViewTypeRegistry;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // View types registry
    $services->set('ekyna_commerce.registry.view_type', ViewTypeRegistry::class);

    // View builder
    $services
        ->set('ekyna_commerce.builder.view', ViewBuilder::class)
        ->lazy()
        ->args([
            service('ekyna_commerce.registry.view_type'),
            service('ekyna_commerce.factory.amount_calculator'),
            service('ekyna_commerce.factory.margin_calculator'),
            service('ekyna_commerce.converter.currency'),
            service('ekyna_commerce.calculator.weight'),
            service('ekyna_commerce.factory.formatter'),
            service('ekyna_commerce.helper.sale_item'),
        ]);

    // Availability view type
    $services
        ->set('ekyna_commerce.view_type.availability', AvailabilityViewType::class)
        ->args([
            service('ekyna_commerce.helper.subject'),
            service('ekyna_commerce.helper.availability'),
        ])
        ->tag(RegisterViewTypePass::VIEW_TYPE_TAG);

    // Abstract view type
    $services
        ->set('ekyna_commerce.view_type.abstract', AbstractViewType::class)
        ->abstract()
        ->call('setUrlGenerator', [service('router')])
        ->call('setTranslator', [service('translator')])
        ->call('setResourceHelper', [service('ekyna_resource.helper')])
        ->call('setSubjectHelper', [service('ekyna_commerce.helper.subject')]);

    // Sale view type
    $services
        ->set('ekyna_commerce.view_type.sale', SaleViewType::class)
        ->parent('ekyna_commerce.view_type.abstract')
        ->call('setLocaleProvider', [service('ekyna_resource.provider.locale')])
        ->call('setAmountCalculatorFactory', [service('ekyna_commerce.factory.amount_calculator')])
        ->call('setAuthorizationChecker', [service('security.authorization_checker')])
        ->tag(RegisterViewTypePass::VIEW_TYPE_TAG);

    // Sale privacy view type
    $services
        ->set('ekyna_commerce.view_type.sale_privacy', SaleViewPrivacyType::class) // TODO Rename to SalePrivacyViewType
        ->parent('ekyna_commerce.view_type.abstract')
        ->tag(RegisterViewTypePass::VIEW_TYPE_TAG);

    // Cart view type
    $services
        ->set('ekyna_commerce.view_type.cart', CartViewType::class)
        ->parent('ekyna_commerce.view_type.abstract')
        ->call('setShipmentPriceResolver', [service('ekyna_commerce.resolver.shipment_price')])
        ->tag(RegisterViewTypePass::VIEW_TYPE_TAG);

    // Quote admin view type
    $services
        ->set('ekyna_commerce.view_type.quote_admin', QuoteAdminViewType::class)
        ->parent('ekyna_commerce.view_type.abstract')
        ->call('setShipmentPriceResolver', [service('ekyna_commerce.resolver.shipment_price')])
        ->tag(RegisterViewTypePass::VIEW_TYPE_TAG);

    // Quote account view type
    $services
        ->set('ekyna_commerce.view_type.quote_account', QuoteAccountViewType::class)
        ->parent('ekyna_commerce.view_type.abstract')
        ->tag(RegisterViewTypePass::VIEW_TYPE_TAG);

    // Order view type
    $services
        ->set('ekyna_commerce.view_type.order', OrderViewType::class)
        ->parent('ekyna_commerce.view_type.abstract')
        ->call('setPrioritizeChecker', [service('ekyna_commerce.prioritizer.checker')])
        ->call('setStockRenderer', [service('ekyna_commerce.renderer.stock')])
        ->call('setInvoiceCalculator', [service('ekyna_commerce.calculator.invoice_subject')])
        ->call('setShipmentSubjectCalculator', [service('ekyna_commerce.calculator.shipment_subject')])
        ->call('setShipmentPriceResolver', [service('ekyna_commerce.resolver.shipment_price')])
        ->tag(RegisterViewTypePass::VIEW_TYPE_TAG);
};
