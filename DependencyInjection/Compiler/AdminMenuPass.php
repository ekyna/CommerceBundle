<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler;

use Ekyna\Component\Commerce\Features;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class AdminMenuPass
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdminMenuPass implements CompilerPassInterface
{
    private Features $features;

    public function process(ContainerBuilder $container): void
    {
        $this->features = new Features($container->getParameter('ekyna_commerce.features'));

        $pool = $container->getDefinition('ekyna_admin.menu.pool');

        $this->addSalesMenu($pool);
        $this->addSupplierMenu($pool);
        $this->addMarketingMenu($pool);
        $this->addSettingMenu($pool);
    }

    private function addSalesMenu(Definition $pool): void
    {
        $pool->addMethodCall('createGroup', [
            [
                'name'     => 'sales',
                'label'    => 'sale.label.plural',
                'domain'   => 'EkynaCommerce',
                'icon'     => 'shopping-cart',
                'position' => 10,
            ],
        ]);

        // Orders
        $pool->addMethodCall('createEntry', [
            'sales',
            [
                'name'     => 'orders',
                'resource' => 'ekyna_commerce.order',
                'position' => 1,
            ],
        ]);
        $pool->addMethodCall('createEntry', [
            'sales',
            [
                'name'     => 'quotes',
                'resource' => 'ekyna_commerce.quote',
                'position' => 2,
            ],
        ]);
        $pool->addMethodCall('createEntry', [
            'sales',
            [
                'name'     => 'carts',
                'resource' => 'ekyna_commerce.cart',
                'position' => 3,
            ],
        ]);

        // Customers
        $pool->addMethodCall('createEntry', [
            'sales',
            [
                'name'     => 'customers',
                'resource' => 'ekyna_commerce.customer',
                'position' => 10,
            ],
        ]);

        // Lists
        $pool->addMethodCall('createEntry', [
            'sales',
            [
                'name'       => 'order_invoices',
                'route'      => 'admin_ekyna_commerce_list_order_invoice',
                'label'      => 'invoice.label.plural',
                'domain'     => 'EkynaCommerce',
                'resource'   => 'ekyna_commerce.order_invoice',
                'permission' => Permission::LIST,
                'position'   => 20,
            ],
        ]);
        $pool->addMethodCall('createEntry', [
            'sales',
            [
                'name'       => 'order_payments',
                'route'      => 'admin_ekyna_commerce_list_order_payment',
                'label'      => 'payment.label.plural',
                'domain'     => 'EkynaCommerce',
                'resource'   => 'ekyna_commerce.order_payment',
                'permission' => Permission::LIST,
                'position'   => 21,
            ],
        ]);
        $pool->addMethodCall('createEntry', [
            'sales',
            [
                'name'       => 'order_shipments',
                'route'      => 'admin_ekyna_commerce_list_order_shipment',
                'label'      => 'shipment.label.plural',
                'domain'     => 'EkynaCommerce',
                'resource'   => 'ekyna_commerce.order_shipment',
                'permission' => Permission::LIST,
                'position'   => 22,
            ],
        ]);

        if ($this->features->isEnabled(Features::SUPPORT)) {
            $pool->addMethodCall('createEntry', [
                'sales',
                [
                    'name'     => 'tickets',
                    'resource' => 'ekyna_commerce.ticket',
                    'position' => 23,
                ],
            ]);
        }
    }

    private function addSupplierMenu(Definition $pool): void
    {
        $pool->addMethodCall('createGroup', [
            [
                'name'     => 'suppliers',
                'label'    => 'supplier.label.plural',
                'domain'   => 'EkynaCommerce',
                'icon'     => 'truck',
                'position' => 11,
            ],
        ]);

        // Supplier orders
        $pool->addMethodCall('createEntry', [
            'suppliers',
            [
                'name'     => 'supplier_orders',
                'resource' => 'ekyna_commerce.supplier_order',
                'position' => 1,
            ],
        ]);

        // Suppliers
        $pool->addMethodCall('createEntry', [
            'suppliers',
            [
                'name'     => 'suppliers',
                'resource' => 'ekyna_commerce.supplier',
                'position' => 2,
            ],
        ]);

        // Supplier carriers
        $pool->addMethodCall('createEntry', [
            'suppliers',
            [
                'name'     => 'supplier_carriers',
                'resource' => 'ekyna_commerce.supplier_carrier',
                'position' => 3,
            ],
        ]);

        // Supplier templates
        $pool->addMethodCall('createEntry', [
            'suppliers',
            [
                'name'     => 'supplier_templates',
                'resource' => 'ekyna_commerce.supplier_template',
                'position' => 4,
            ],
        ]);

        // Warehouse
        $pool->addMethodCall('createEntry', [
            'suppliers',
            [
                'name'     => 'warehouses',
                'resource' => 'ekyna_commerce.warehouse',
                'position' => 5,
            ],
        ]);
    }

    private function addMarketingMenu(Definition $pool): void
    {
        if (!$this->features->isEnabled(Features::NEWSLETTER) && !$this->features->isEnabled(Features::COUPON)) {
            return;
        }

        $pool->addMethodCall('createGroup', [
            [
                'name'     => 'marketing',
                'label'    => 'marketing.title',
                'domain'   => 'EkynaCommerce',
                'icon'     => 'tags',
                'position' => 12,
            ],
        ]);

        if ($this->features->isEnabled(Features::NEWSLETTER)) {
            // Audience
            $pool->addMethodCall('createEntry', [
                'marketing',
                [
                    'name'     => 'audiences',
                    'resource' => 'ekyna_commerce.audience',
                    'position' => 1,
                ],
            ]);

            // Member
            $pool->addMethodCall('createEntry', [
                'marketing',
                [
                    'name'     => 'members',
                    'resource' => 'ekyna_commerce.member',
                    'position' => 2,
                ],
            ]);
        }

        if ($this->features->isEnabled(Features::COUPON)) {
            // Coupon
            $pool->addMethodCall('createEntry', [
                'marketing',
                [
                    'name'     => 'coupons',
                    'resource' => 'ekyna_commerce.coupon',
                    'position' => 3,
                ],
            ]);
        }
    }

    private function addSettingMenu(Definition $pool): void
    {
        $pool->addMethodCall('createGroup', [
            [
                'name'     => 'setting',
                'label'    => 'setting',
                'domain'   => 'EkynaSetting',
                'icon'     => 'cogs',
                'position' => 100,
            ],
        ]);

        // Customer groups
        $pool->addMethodCall('createEntry', [
            'setting',
            [
                'name'     => 'customer_groups',
                'resource' => 'ekyna_commerce.customer_group',
                'position' => 40,
            ],
        ]);

        // Payment terms
        $pool->addMethodCall('createEntry', [
            'setting',
            [
                'name'     => 'payment_terms',
                'resource' => 'ekyna_commerce.payment_term',
                'position' => 50,
            ],
        ]);

        // Payment methods
        $pool->addMethodCall('createEntry', [
            'setting',
            [
                'name'     => 'payment_methods',
                'resource' => 'ekyna_commerce.payment_method',
                'position' => 51,
            ],
        ]);

        // Shipment methods
        $pool->addMethodCall('createEntry', [
            'setting',
            [
                'name'     => 'shipment_methods',
                'resource' => 'ekyna_commerce.shipment_method',
                'position' => 52,
            ],
        ]);

        // Shipment zones
        $pool->addMethodCall('createEntry', [
            'setting',
            [
                'name'     => 'shipment_zones',
                'resource' => 'ekyna_commerce.shipment_zone',
                'position' => 53,
            ],
        ]);

        // Shipment rules
        $pool->addMethodCall('createEntry', [
            'setting',
            [
                'name'     => 'shipment_rules',
                'resource' => 'ekyna_commerce.shipment_rule',
                'position' => 54,
            ],
        ]);

        // Notify models
        $pool->addMethodCall('createEntry', [
            'setting',
            [
                'name'     => 'notify_models',
                'resource' => 'ekyna_commerce.notify_model',
                'position' => 55,
            ],
        ]);

        // Tax groups
        $pool->addMethodCall('createEntry', [
            'setting',
            [
                'name'     => 'tax_groups',
                'resource' => 'ekyna_commerce.tax_group',
                'position' => 70,
            ],
        ]);

        // Tax rules
        $pool->addMethodCall('createEntry', [
            'setting',
            [
                'name'     => 'tax_rules',
                'resource' => 'ekyna_commerce.tax_rule',
                'position' => 71,
            ],
        ]);

        // Taxes
        $pool->addMethodCall('createEntry', [
            'setting',
            [
                'name'     => 'taxes',
                'resource' => 'ekyna_commerce.tax',
                'position' => 72,
            ],
        ]);

        // Accounting
        $pool->addMethodCall('createEntry', [
            'setting',
            [
                'name'     => 'accounting',
                'resource' => 'ekyna_commerce.accounting',
                'position' => 80,
            ],
        ]);

        // Countries
        $pool->addMethodCall('createEntry', [
            'setting',
            [
                'name'     => 'countries',
                'resource' => 'ekyna_commerce.country',
                'position' => 98,
            ],
        ]);

        // Currencies
        $pool->addMethodCall('createEntry', [
            'setting',
            [
                'name'     => 'currencies',
                'resource' => 'ekyna_commerce.currency',
                'position' => 99,
            ],
        ]);
    }
}
