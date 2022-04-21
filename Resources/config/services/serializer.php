<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\CommerceBundle\Service\Serializer\BalanceNormalizer;
use Ekyna\Bundle\CommerceBundle\Service\Serializer\SaleNormalizer;
use Ekyna\Bundle\CommerceBundle\Service\Serializer\StockAdjustmentNormalizer;
use Ekyna\Bundle\CommerceBundle\Service\Serializer\StockAssignmentNormalizer;
use Ekyna\Bundle\CommerceBundle\Service\Serializer\StockUnitNormalizer;
use Ekyna\Bundle\CommerceBundle\Service\Serializer\TicketAttachmentNormalizer;
use Ekyna\Bundle\CommerceBundle\Service\Serializer\TicketMessageNormalizer;
use Ekyna\Bundle\CommerceBundle\Service\Serializer\TicketNormalizer;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Helper\SubjectNormalizerHelper;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\AddressNormalizer;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\InvoiceItemNormalizer;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\InvoiceLineNormalizer;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\InvoiceNormalizer;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\RelayPointNormalizer;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\SaleItemNormalizer;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\ShipmentItemNormalizer;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\ShipmentNormalizer;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\ShipmentParcelNormalizer;
use Ekyna\Component\Commerce\Bridge\Symfony\Serializer\Normalizer\SupplierOrderItemNormalizer;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceItemInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentParcelInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Address normalizer
        ->set('ekyna_commerce.normalizer.address', AddressNormalizer::class)
            ->parent('ekyna_resource.normalizer.abstract')
            ->args([
                service('ekyna_commerce.transformer.array_address'),
                service('libphonenumber\PhoneNumberUtil'),
            ])
            ->call('setClass', [AddressInterface::class])
            ->tag('serializer.normalizer')
            ->tag('serializer.denormalizer')

        // Balance normalizer
        ->set('ekyna_commerce.normalizer.balance', BalanceNormalizer::class)
            ->args([
                service('translator'),
                service('router'),
            ])
            ->call('setFormatterFactory', [service('ekyna_commerce.factory.formatter')])
            ->tag('serializer.normalizer', ['priority' => 1024])

        // Invoice normalizer
        ->set('ekyna_commerce.normalizer.invoice', InvoiceNormalizer::class)
            ->parent('ekyna_resource.normalizer.abstract')
            ->call('setClass', [InvoiceInterface::class])
            ->tag('serializer.normalizer')
            ->tag('serializer.denormalizer')

        // Invoice line normalizer
        ->set('ekyna_commerce.normalizer.invoice_line', InvoiceLineNormalizer::class)
            ->parent('ekyna_resource.normalizer.abstract')
            ->call('setClass', [InvoiceLineInterface::class])
            ->tag('serializer.normalizer')
            ->tag('serializer.denormalizer')

        // Invoice item normalizer
        ->set('ekyna_commerce.normalizer.invoice_item', InvoiceItemNormalizer::class)
            ->parent('ekyna_resource.normalizer.abstract')
            ->call('setClass', [InvoiceItemInterface::class])
            ->tag('serializer.normalizer')
            ->tag('serializer.denormalizer')

        // Relay point normalizer
        ->set('ekyna_commerce.normalizer.relay_point', RelayPointNormalizer::class)
            ->args([
                service('ekyna_resource.provider.locale'),
            ])
            ->tag('serializer.normalizer', ['priority' => 1024])
            ->tag('serializer.denormalizer', ['priority' => 1024])

        // Sale normalizer
        ->set('ekyna_commerce.normalizer.sale', SaleNormalizer::class)
            ->parent('ekyna_resource.normalizer.abstract')
            ->args([
                service('ekyna_commerce.helper.constants'),
                service('ekyna_commerce.renderer.flag'),
            ])
            ->call('setClass', [SaleInterface::class])
            ->tag('serializer.normalizer')
            ->tag('serializer.denormalizer')

        // Sale item normalizer
        ->set('ekyna_commerce.normalizer.sale_item', SaleItemNormalizer::class)
            ->parent('ekyna_resource.normalizer.abstract')
            ->args([
                service('ekyna_commerce.calculator.shipment_subject'),
                service('ekyna_commerce.calculator.invoice_subject'),
                service('ekyna_commerce.helper.subject'),
            ])
            ->call('setClass', [SaleItemInterface::class])
            ->tag('serializer.normalizer')
            ->tag('serializer.denormalizer')

        // Shipment normalizer
        ->set('ekyna_commerce.normalizer.shipment', ShipmentNormalizer::class)
            ->parent('ekyna_resource.normalizer.abstract')
            ->args([
                service('ekyna_commerce.resolver.shipment_address'),
                service('ekyna_commerce.calculator.shipment_weight'),
            ])
            ->call('setClass', [ShipmentInterface::class])
            ->tag('serializer.normalizer')
            ->tag('serializer.denormalizer')

        // Shipment item normalizer
        ->set('ekyna_commerce.normalizer.shipment_item', ShipmentItemNormalizer::class)
            ->parent('ekyna_resource.normalizer.abstract')
            ->call('setClass', [ShipmentItemInterface::class])
            ->tag('serializer.normalizer')
            ->tag('serializer.denormalizer')

        // Shipment parcel normalizer
        ->set('ekyna_commerce.normalizer.shipment_parcel', ShipmentParcelNormalizer::class)
            ->parent('ekyna_resource.normalizer.abstract')
            ->call('setClass', [ShipmentParcelInterface::class])
            ->tag('serializer.normalizer')
            ->tag('serializer.denormalizer')

        // Stock unit normalizer
        ->set('ekyna_commerce.normalizer.stock_unit', StockUnitNormalizer::class)
            ->parent('ekyna_resource.normalizer.abstract')
            ->args([
                service('ekyna_commerce.factory.formatter'),
                service('ekyna_commerce.converter.currency'),
                service('ekyna_commerce.helper.constants'),
                service('ekyna_resource.helper'),
            ])
            ->call('setClass', [StockUnitInterface::class])
            ->tag('serializer.normalizer')
            ->tag('serializer.denormalizer')

        // Stock adjustment normalizer
        ->set('ekyna_commerce.normalizer.stock_adjustment', StockAdjustmentNormalizer::class)
            ->parent('ekyna_resource.normalizer.abstract')
            ->args([
                service('ekyna_commerce.factory.formatter'),
                service('ekyna_commerce.helper.constants'),
                service('ekyna_resource.helper'),
            ])
            ->call('setClass', [StockAdjustmentInterface::class])
            ->tag('serializer.normalizer')
            ->tag('serializer.denormalizer')

        // Stock assignment normalizer
        ->set('ekyna_commerce.normalizer.stock_assignment', StockAssignmentNormalizer::class)
            ->parent('ekyna_resource.normalizer.abstract')
            ->args([
                service('ekyna_commerce.factory.formatter'),
                service('ekyna_commerce.helper.constants'),
                service('ekyna_resource.helper'),
            ])
            ->call('setClass', [StockAssignmentInterface::class])
            ->tag('serializer.normalizer')
            ->tag('serializer.denormalizer')

        // Subject normalization helper
        // TODO previously ekyna_commerce.normalizer.subject_helper
        ->set('ekyna_commerce.helper.subject_normalizer', SubjectNormalizerHelper::class)
            ->lazy(true)
            ->args([
                service('ekyna_commerce.factory.formatter'),
                service('ekyna_commerce.helper.constants'),
                service('ekyna_resource.helper'),
                service('ekyna_resource.repository.factory'),
            ])
            ->call('setNormalizer', [service('serializer')])

        // Supplier order item normalizer
        ->set('ekyna_commerce.normalizer.supplier_order_item', SupplierOrderItemNormalizer::class)
            ->args([
                service('ekyna_commerce.factory.formatter'),
            ])

        // Ticket normalizer
        ->set('ekyna_commerce.normalizer.ticket', TicketNormalizer::class)
            ->call('setFormatterFactory', [service('ekyna_commerce.factory.formatter')])
            ->call('setAuthorization', [service('security.authorization_checker')])
            ->call('setTranslator', [service('translator')])

        // Ticket message normalizer
        ->set('ekyna_commerce.normalizer.ticket_message', TicketMessageNormalizer::class)
            ->call('setFormatterFactory', [service('ekyna_commerce.factory.formatter')])
            ->call('setAuthorization', [service('security.authorization_checker')])

        // Ticket attachment normalizer
        ->set('ekyna_commerce.normalizer.ticket_attachment', TicketAttachmentNormalizer::class)
            ->call('setFormatterFactory', [service('ekyna_commerce.factory.formatter')])
            ->call('setAuthorization', [service('security.authorization_checker')])
    ;
};
