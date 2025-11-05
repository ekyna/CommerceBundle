<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Validator\Constraints\NotifyModelValidator;
use Ekyna\Bundle\CommerceBundle\Validator\Constraints\PaymentMethodValidator;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\BillOfMaterialsValidator;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\GenderValidator;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\InvoiceLineValidator;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\PaymentValidator;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\ProductionValidator;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\RelayPointValidator;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\SaleItemAvailabilityValidator;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\SaleItemValidator;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\SaleValidator;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\ShipmentItemValidator;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\ShipmentPriceValidator;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\ShipmentValidator;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\SupplierProductValidator;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\SaleStepValidator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // Bill of materials validator
    $services
        ->set('ekyna_commerce.validator.bill_of_materials', BillOfMaterialsValidator::class)
        ->args( [
            service('ekyna_commerce.repository.bill_of_materials'),
            service('ekyna_commerce.helper.subject'),
        ])
        ->tag('validator.constraint_validator');

    // Gender validator
    $services
        ->set('ekyna_commerce.validator.gender', GenderValidator::class)
        ->args([
            param('ekyna_commerce.class.genders'),
        ])
        ->tag('validator.constraint_validator');

    // Invoice line validator
    $services
        ->set('ekyna_commerce.validator.invoice_line', InvoiceLineValidator::class)
        ->args([
            service('ekyna_commerce.calculator.invoice_subject'),
        ])
        ->tag('validator.constraint_validator');

    // Notify model validator
    $services
        ->set('ekyna_commerce.validator.notify_model', NotifyModelValidator::class)
        ->args([
            service('ekyna_commerce.repository.notify_model'),
            param('kernel.default_locale'),
        ])
        ->tag('validator.constraint_validator');

    // Payment validator
    $services
        ->set('ekyna_commerce.validator.payment', PaymentValidator::class)
        ->args([
            service('ekyna_commerce.converter.currency'),
        ])
        ->tag('validator.constraint_validator');

    // Payment method validator
    $services
        ->set('ekyna_commerce.validator.payment_method', PaymentMethodValidator::class)
        ->args([
            service('payum'),
        ])
        ->tag('validator.constraint_validator');

    // Production validator
    $services
        ->set('ekyna_commerce.validator.production', ProductionValidator::class)
        ->args([
            service('ekyna_commerce.calculator.production'),
        ])
        ->tag('validator.constraint_validator');

    // Relay point validator
    $services
        ->set('ekyna_commerce.validator.relay_point', RelayPointValidator::class)
        ->args([
            service('ekyna_commerce.registry.shipment_gateway'),
        ])
        ->tag('validator.constraint_validator');

    // Sale validator
    $services
        ->set('ekyna_commerce.validator.sale', SaleValidator::class)
        ->args([
            service('ekyna_commerce.repository.country'),
            service('ekyna_commerce.registry.shipment_gateway'),
        ])
        ->tag('validator.constraint_validator');

    // Sale item validator
    $services
        ->set('ekyna_commerce.validator.sale_item', SaleItemValidator::class)
        ->args([
            service('ekyna_commerce.calculator.invoice_subject'),
            service('ekyna_commerce.calculator.shipment_subject'),
        ])
        ->tag('validator.constraint_validator');

    // Sale item availability validator
    $services
        ->set('ekyna_commerce.validator.sale_item_availability', SaleItemAvailabilityValidator::class)
        ->args([
            service('ekyna_commerce.helper.subject'),
            service('ekyna_commerce.helper.availability'),
        ])
        ->tag('validator.constraint_validator');

    // Shipment validator
    $services
        ->set('ekyna_commerce.validator.shipment', ShipmentValidator::class)
        ->args([
            service('ekyna_commerce.registry.shipment_gateway'),
        ])
        ->tag('validator.constraint_validator');

    // Shipment validator
    $services
        ->set('ekyna_commerce.validator.shipment', ShipmentValidator::class)
        ->args([
            service('ekyna_commerce.registry.shipment_gateway'),
        ])
        ->tag('validator.constraint_validator');

    // Shipment item validator
    $services
        ->set('ekyna_commerce.validator.shipment_item', ShipmentItemValidator::class)
        ->args([
            service('ekyna_commerce.factory.resolver.shipment_availability'),
        ])
        ->tag('validator.constraint_validator');

    // Shipment price validator
    $services
        ->set('ekyna_commerce.validator.shipment_price', ShipmentPriceValidator::class)
        ->args([
            service('ekyna_commerce.registry.shipment_gateway'),
        ])
        ->tag('validator.constraint_validator');

    // Shipment product validator
    $services
        ->set('ekyna_commerce.validator.supplier_product', SupplierProductValidator::class)
        ->args([
            service('ekyna_commerce.repository.supplier_product'),
            service('ekyna_commerce.helper.subject'),
        ])
        ->tag('validator.constraint_validator');

    // Sale step validator
    $services
        ->set('ekyna_commerce.validator.sale_step', SaleStepValidator::class)
        ->args([
            service('validator'),
        ]);
};
