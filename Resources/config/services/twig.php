<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Service\Common\SaleRenderer;
use Ekyna\Bundle\CommerceBundle\Twig\BarcodeExtension;
use Ekyna\Bundle\CommerceBundle\Twig\CheckoutExtension;
use Ekyna\Bundle\CommerceBundle\Twig\CommonExtension;
use Ekyna\Bundle\CommerceBundle\Twig\DocumentExtension;
use Ekyna\Bundle\CommerceBundle\Twig\InvoiceExtension;
use Ekyna\Bundle\CommerceBundle\Twig\LoyaltyExtension;
use Ekyna\Bundle\CommerceBundle\Twig\NewsletterExtension;
use Ekyna\Bundle\CommerceBundle\Twig\PaymentExtension;
use Ekyna\Bundle\CommerceBundle\Twig\SaleExtension;
use Ekyna\Bundle\CommerceBundle\Twig\ShipmentExtension;
use Ekyna\Bundle\CommerceBundle\Twig\StockExtension;
use Ekyna\Bundle\CommerceBundle\Twig\SubjectExtension;
use Ekyna\Bundle\CommerceBundle\Twig\SupplierExtension;
use Ekyna\Bundle\CommerceBundle\Twig\SupportExtension;
use Ekyna\Bundle\CommerceBundle\Twig\WidgetExtension;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Barcode extension
        ->set('ekyna_commerce.twig_extension.barcode', BarcodeExtension::class)
            ->tag('twig.extension')

        // Checkout extension
        ->set('ekyna_commerce.twig_extension.checkout', CheckoutExtension::class)
            ->tag('twig.extension')

        // Common extension
        ->set('ekyna_commerce.twig_extension.common', CommonExtension::class)
            ->tag('twig.extension')

        // Widget extension
        ->set('ekyna_commerce.twig_extension.widget', WidgetExtension::class)
            ->tag('twig.extension')

        // Document extension
        ->set('ekyna_commerce.twig_extension.document', DocumentExtension::class)
            ->tag('twig.extension')

        // Invoice extension
        ->set('ekyna_commerce.twig_extension.invoice', InvoiceExtension::class)
            ->tag('twig.extension')

        // Loyalty extension
        ->set('ekyna_commerce.twig_extension.loyalty', LoyaltyExtension::class)
            ->tag('twig.extension')

        // Newsletter extension
        ->set('ekyna_commerce.twig_extension.newsletter', NewsletterExtension::class)
            ->tag('twig.extension')

        // Payment extension
        ->set('ekyna_commerce.twig_extension.payment', PaymentExtension::class)
            ->tag('twig.extension')

        // Sale helper and extension
        ->set('ekyna_commerce.twig_extension.sale', SaleExtension::class)
            ->tag('twig.extension')

        // Shipment extension
        ->set('ekyna_commerce.twig_extension.shipment', ShipmentExtension::class)
            ->tag('twig.extension')

        // Stock extension
        ->set('ekyna_commerce.twig_extension.stock', StockExtension::class)
            ->tag('twig.extension')

        // Subject extension
        ->set('ekyna_commerce.twig_extension.subject', SubjectExtension::class)
            ->tag('twig.extension')

        // Supplier extension
        ->set('ekyna_commerce.twig_extension.supplier', SupplierExtension::class)
            ->tag('twig.extension')

        // Support extension
        ->set('ekyna_commerce.twig_extension.support', SupportExtension::class)
            ->tag('twig.extension')
    ;
};
