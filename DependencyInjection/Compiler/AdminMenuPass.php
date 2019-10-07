<?php

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler;

use Ekyna\Component\Commerce\Features;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class AdminMenuPass
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdminMenuPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ekyna_admin.menu.pool')) {
            return;
        }

        $features = new Features($container->getParameter('ekyna_commerce.features'));

        $pool = $container->getDefinition('ekyna_admin.menu.pool');

        $pool->addMethodCall('createGroup', [[
            'name'     => 'sales',
            'label'    => 'ekyna_commerce.sale.label.plural',
            'icon'     => 'shopping-cart',
            'position' => 10,
        ]]);

        // Orders
        $pool->addMethodCall('createEntry', ['sales', [
            'name'     => 'orders',
            'route'    => 'ekyna_commerce_order_admin_list',
            'label'    => 'ekyna_commerce.order.label.plural',
            'resource' => 'ekyna_commerce_order',
            'position' => 1,
        ]]);
        $pool->addMethodCall('createEntry', ['sales', [
            'name'     => 'quotes',
            'route'    => 'ekyna_commerce_quote_admin_list',
            'label'    => 'ekyna_commerce.quote.label.plural',
            'resource' => 'ekyna_commerce_quote',
            'position' => 2,
        ]]);
        $pool->addMethodCall('createEntry', ['sales', [
            'name'     => 'carts',
            'route'    => 'ekyna_commerce_cart_admin_list',
            'label'    => 'ekyna_commerce.cart.label.plural',
            'resource' => 'ekyna_commerce_cart',
            'position' => 3,
        ]]);

        // Customers
        $pool->addMethodCall('createEntry', ['sales', [
            'name'     => 'customers',
            'route'    => 'ekyna_commerce_customer_admin_list',
            'label'    => 'ekyna_commerce.customer.label.plural',
            'resource' => 'ekyna_commerce_customer',
            'position' => 10,
        ]]);

        // Lists
        $pool->addMethodCall('createEntry', ['sales', [
            'name'     => 'order_invoices',
            'route'    => 'ekyna_commerce_admin_order_list_invoice',
            'label'    => 'ekyna_commerce.invoice.label.plural',
            'resource' => 'ekyna_commerce_order_invoice',
            'position' => 20,
        ]]);
        $pool->addMethodCall('createEntry', ['sales', [
            'name'     => 'order_payments',
            'route'    => 'ekyna_commerce_admin_order_list_payment',
            'label'    => 'ekyna_commerce.payment.label.plural',
            'resource' => 'ekyna_commerce_order_payment',
            'position' => 21,
        ]]);
        $pool->addMethodCall('createEntry', ['sales', [
            'name'     => 'order_shipments',
            'route'    => 'ekyna_commerce_admin_order_list_shipment',
            'label'    => 'ekyna_commerce.shipment.label.plural',
            'resource' => 'ekyna_commerce_order_shipment',
            'position' => 22,
        ]]);
        // TODO if ($features->isEnabled(Features::SUPPORT)) {
        if ($container->getParameter('ekyna_commerce.support.enabled')) {
            $pool->addMethodCall('createEntry', ['sales', [
                'name'     => 'tickets',
                'route'    => 'ekyna_commerce_ticket_admin_list',
                'label'    => 'ekyna_commerce.ticket.label.plural',
                'resource' => 'ekyna_commerce_ticket',
                'position' => 23,
            ]]);
        }
        if ($features->isEnabled(Features::COUPON)) {
            $pool->addMethodCall('createEntry', ['sales', [
                'name'     => 'coupons',
                'route'    => 'ekyna_commerce_coupon_admin_list',
                'label'    => 'ekyna_commerce.coupon.label.plural',
                'resource' => 'ekyna_commerce_coupon',
                'position' => 24,
            ]]);
        }

        // ------------------------------------------------------------

        $pool->addMethodCall('createGroup', [[
            'name'     => 'suppliers',
            'label'    => 'ekyna_commerce.supplier.label.plural',
            'icon'     => 'truck',
            'position' => 11,
        ]]);

        // Supplier orders
        $pool->addMethodCall('createEntry', ['suppliers', [
            'name'     => 'supplier_orders',
            'route'    => 'ekyna_commerce_supplier_order_admin_list',
            'label'    => 'ekyna_commerce.supplier_order.label.plural',
            'resource' => 'ekyna_commerce_supplier_order',
            'position' => 1,
        ]]);

        // Suppliers
        $pool->addMethodCall('createEntry', ['suppliers', [
            'name'     => 'suppliers',
            'route'    => 'ekyna_commerce_supplier_admin_list',
            'label'    => 'ekyna_commerce.supplier.label.plural',
            'resource' => 'ekyna_commerce_supplier',
            'position' => 2,
        ]]);

        // Supplier carriers
        $pool->addMethodCall('createEntry', ['suppliers', [
            'name'     => 'supplier_carriers',
            'route'    => 'ekyna_commerce_supplier_carrier_admin_list',
            'label'    => 'ekyna_commerce.supplier_carrier.label.plural',
            'resource' => 'ekyna_commerce_supplier_carrier',
            'position' => 3,
        ]]);

        // Supplier templates
        $pool->addMethodCall('createEntry', ['suppliers', [
            'name'     => 'supplier_templates',
            'route'    => 'ekyna_commerce_supplier_template_admin_list',
            'label'    => 'ekyna_commerce.supplier_template.label.plural',
            'resource' => 'ekyna_commerce_supplier_template',
            'position' => 4,
        ]]);

        // Warehouse
        $pool->addMethodCall('createEntry', ['suppliers', [
            'name'     => 'warehouses',
            'route'    => 'ekyna_commerce_warehouse_admin_list',
            'label'    => 'ekyna_commerce.warehouse.label.plural',
            'resource' => 'ekyna_commerce_warehouse',
            'position' => 5,
        ]]);

        // ------------------------------------------------------------

        $pool->addMethodCall('createGroup', [[
            'name'     => 'setting',
            'label'    => 'ekyna_setting.label',
            'icon'     => 'cogs',
            'position' => 100,
        ]]);

        // Customer groups
        $pool->addMethodCall('createEntry', ['setting', [
            'name'     => 'customer_groups',
            'route'    => 'ekyna_commerce_customer_group_admin_list',
            'label'    => 'ekyna_commerce.customer_group.label.plural',
            'resource' => 'ekyna_commerce_customer_group',
            'position' => 40,
        ]]);

        // Payment / Shipment methods
        $pool->addMethodCall('createEntry', ['setting', [
            'name'     => 'payment_term',
            'route'    => 'ekyna_commerce_payment_term_admin_list',
            'label'    => 'ekyna_commerce.payment_term.label.plural',
            'resource' => 'ekyna_commerce_payment_term',
            'position' => 50,
        ]]);
        $pool->addMethodCall('createEntry', ['setting', [
            'name'     => 'payment_method',
            'route'    => 'ekyna_commerce_payment_method_admin_list',
            'label'    => 'ekyna_commerce.payment_method.label.plural',
            'resource' => 'ekyna_commerce_payment_method',
            'position' => 51,
        ]]);
        $pool->addMethodCall('createEntry', ['setting', [
            'name'     => 'shipment_method',
            'route'    => 'ekyna_commerce_shipment_method_admin_list',
            'label'    => 'ekyna_commerce.shipment_method.label.plural',
            'resource' => 'ekyna_commerce_shipment_method',
            'position' => 52,
        ]]);
        $pool->addMethodCall('createEntry', ['setting', [
            'name'     => 'shipment_zone',
            'route'    => 'ekyna_commerce_shipment_zone_admin_list',
            'label'    => 'ekyna_commerce.shipment_zone.label.plural',
            'resource' => 'ekyna_commerce_shipment_zone',
            'position' => 53,
        ]]);
        $pool->addMethodCall('createEntry', ['setting', [
            'name'     => 'shipment_rule',
            'route'    => 'ekyna_commerce_shipment_rule_admin_list',
            'label'    => 'ekyna_commerce.shipment_rule.label.plural',
            'resource' => 'ekyna_commerce_shipment_rule',
            'position' => 54,
        ]]);
        $pool->addMethodCall('createEntry', ['setting', [
            'name'     => 'notify_model',
            'route'    => 'ekyna_commerce_notify_model_admin_list',
            'label'    => 'ekyna_commerce.notify_model.label.plural',
            'resource' => 'ekyna_commerce_notify_model',
            'position' => 55,
        ]]);

        // Tax groups / Tax rules / Taxes
        $pool->addMethodCall('createEntry', ['setting', [
            'name'     => 'tax_group',
            'route'    => 'ekyna_commerce_tax_group_admin_list',
            'label'    => 'ekyna_commerce.tax_group.label.plural',
            'resource' => 'ekyna_commerce_tax_group',
            'position' => 70,
        ]]);
        $pool->addMethodCall('createEntry', ['setting', [
            'name'     => 'tax_rule',
            'route'    => 'ekyna_commerce_tax_rule_admin_list',
            'label'    => 'ekyna_commerce.tax_rule.label.plural',
            'resource' => 'ekyna_commerce_tax_rule',
            'position' => 71,
        ]]);
        $pool->addMethodCall('createEntry', ['setting', [
            'name'     => 'taxes',
            'route'    => 'ekyna_commerce_tax_admin_list',
            'label'    => 'ekyna_commerce.tax.label.plural',
            'resource' => 'ekyna_commerce_tax',
            'position' => 72,
        ]]);

        // Accounting
        $pool->addMethodCall('createEntry', ['setting', [
            'name'     => 'accounting',
            'route'    => 'ekyna_commerce_accounting_admin_list',
            'label'    => 'ekyna_commerce.accounting.label.plural',
            'resource' => 'ekyna_commerce_accounting',
            'position' => 80,
        ]]);

        // Countries / Currencies
        $pool->addMethodCall('createEntry', ['setting', [
            'name'     => 'countries',
            'route'    => 'ekyna_commerce_country_admin_list',
            'label'    => 'ekyna_commerce.country.label.plural',
            'resource' => 'ekyna_commerce_country',
            'position' => 98,
        ]]);
        $pool->addMethodCall('createEntry', ['setting', [
            'name'     => 'currencies',
            'route'    => 'ekyna_commerce_currency_admin_list',
            'label'    => 'ekyna_commerce.currency.label.plural',
            'resource' => 'ekyna_commerce_currency',
            'position' => 99,
        ]]);
    }
}
