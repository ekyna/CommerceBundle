<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler;

use Ekyna\Bundle\AdminBundle\Service\Menu\PoolHelper;
use Ekyna\Bundle\SettingBundle\DependencyInjection\Compiler\AdminMenuPass as SettingPass;
use Ekyna\Component\Commerce\Features;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class AdminMenuPass
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdminMenuPass implements CompilerPassInterface
{
    public const SALES_GROUP = [
        'name'     => 'sales',
        'label'    => 'label',
        'domain'   => 'EkynaCommerce',
        'icon'     => 'shopping-cart',
        'position' => 10,
    ];

    public const SUPPLIERS_GROUP = [
        'name'     => 'suppliers',
        'label'    => 'supplier.label.plural',
        'domain'   => 'EkynaCommerce',
        'icon'     => 'truck',
        'position' => 11,
    ];

    public const MARKETING_GROUP = [
        'name'     => 'marketing',
        'label'    => 'marketing.title',
        'domain'   => 'EkynaCommerce',
        'icon'     => 'tags',
        'position' => 12,
    ];

    private readonly PoolHelper $helper;
    private readonly Features   $features;

    public function process(ContainerBuilder $container): void
    {
        $this->helper = new PoolHelper($container->getDefinition('ekyna_admin.menu.pool'));
        $this->features = new Features($container->getParameter('ekyna_commerce.features'));

        $this->addSalesMenu();
        $this->addSupplierMenu();
        $this->addMarketingMenu();
        $this->addSettingMenu();
    }

    private function addSalesMenu(): void
    {
        $this
            ->helper
            ->addGroup(self::SALES_GROUP)
            ->addEntry([
                'name'     => 'orders',
                'resource' => 'ekyna_commerce.order',
                'position' => 1,
            ])
            ->addEntry([
                'name'     => 'quotes',
                'resource' => 'ekyna_commerce.quote',
                'position' => 2,
            ])
            ->addEntry([
                'name'     => 'carts',
                'resource' => 'ekyna_commerce.cart',
                'position' => 3,
            ])
            ->addEntry([
                'name'     => 'customers',
                'resource' => 'ekyna_commerce.customer',
                'position' => 10,
            ])
            ->addEntry([
                'name'     => 'projects',
                'resource' => 'ekyna_commerce.project',
                'position' => 11,
            ])
            ->addEntry([
                'name'       => 'order_invoices',
                'route'      => 'admin_ekyna_commerce_list_order_invoice',
                'label'      => 'invoice.label.plural',
                'domain'     => 'EkynaCommerce',
                'resource'   => 'ekyna_commerce.order_invoice',
                'permission' => Permission::LIST,
                'position'   => 20,
            ])
            ->addEntry([
                'name'       => 'order_payments',
                'route'      => 'admin_ekyna_commerce_list_order_payment',
                'label'      => 'payment.label.plural',
                'domain'     => 'EkynaCommerce',
                'resource'   => 'ekyna_commerce.order_payment',
                'permission' => Permission::LIST,
                'position'   => 21,
            ])
            ->addEntry([
                'name'       => 'order_shipments',
                'route'      => 'admin_ekyna_commerce_list_order_shipment',
                'label'      => 'shipment.label.plural',
                'domain'     => 'EkynaCommerce',
                'resource'   => 'ekyna_commerce.order_shipment',
                'permission' => Permission::LIST,
                'position'   => 22,
            ]);

        if (!$this->features->isEnabled(Features::SUPPORT)) {
            return;
        }

        $this
            ->helper
            ->addEntry([
                'name'     => 'tickets',
                'resource' => 'ekyna_commerce.ticket',
                'position' => 23,
            ])
            ->addEntry([
                'name'     => 'ticket_tags',
                'resource' => 'ekyna_commerce.ticket_tag',
                'position' => 24,
            ]);
    }

    private function addSupplierMenu(): void
    {
        $this
            ->helper
            ->addGroup(self::SUPPLIERS_GROUP)
            ->addEntry([
                'name'     => 'supplier_orders',
                'resource' => 'ekyna_commerce.supplier_order',
                'position' => 1,
            ])
            ->addEntry([
                'name'     => 'suppliers',
                'resource' => 'ekyna_commerce.supplier',
                'position' => 2,
            ])
            ->addEntry([
                'name'     => 'supplier_carriers',
                'resource' => 'ekyna_commerce.supplier_carrier',
                'position' => 3,
            ])
            ->addEntry([
                'name'     => 'supplier_templates',
                'resource' => 'ekyna_commerce.supplier_template',
                'position' => 4,
            ])
            ->addEntry([
                'name'     => 'warehouses',
                'resource' => 'ekyna_commerce.warehouse',
                'position' => 5,
            ]);
    }

    private function addMarketingMenu(): void
    {
        if (!$this->features->isEnabled(Features::NEWSLETTER) && !$this->features->isEnabled(Features::COUPON)) {
            return;
        }

        $this->helper->addGroup(self::MARKETING_GROUP);

        if ($this->features->isEnabled(Features::NEWSLETTER)) {
            $this
                ->helper
                ->addEntry([
                    'name'     => 'audiences',
                    'resource' => 'ekyna_commerce.audience',
                    'position' => 1,
                ])
                ->addEntry([
                    'name'     => 'members',
                    'resource' => 'ekyna_commerce.member',
                    'position' => 2,
                ]);
        }

        if ($this->features->isEnabled(Features::COUPON)) {
            $this
                ->helper
                ->addEntry([
                    'name'     => 'coupons',
                    'resource' => 'ekyna_commerce.coupon',
                    'position' => 3,
                ]);
        }
    }

    private function addSettingMenu(): void
    {
        $this
            ->helper
            ->addGroup(SettingPass::GROUP)
            ->addEntry([
                'name'     => 'customer_groups',
                'resource' => 'ekyna_commerce.customer_group',
                'position' => 40,
            ])
            ->addEntry([
                'name'     => 'customer_positions',
                'resource' => 'ekyna_commerce.customer_position',
                'position' => 41,
            ])
            ->addEntry([
                'name'     => 'payment_terms',
                'resource' => 'ekyna_commerce.payment_term',
                'position' => 50,
            ])
            ->addEntry([
                'name'     => 'payment_methods',
                'resource' => 'ekyna_commerce.payment_method',
                'position' => 51,
            ])
            ->addEntry([
                'name'     => 'shipment_methods',
                'resource' => 'ekyna_commerce.shipment_method',
                'position' => 52,
            ])
            ->addEntry([
                'name'     => 'shipment_zones',
                'resource' => 'ekyna_commerce.shipment_zone',
                'position' => 53,
            ])
            ->addEntry([
                'name'     => 'shipment_rules',
                'resource' => 'ekyna_commerce.shipment_rule',
                'position' => 54,
            ])
            ->addEntry([
                'name'     => 'notify_models',
                'resource' => 'ekyna_commerce.notify_model',
                'position' => 55,
            ])
            ->addEntry([
                'name'     => 'tax_groups',
                'resource' => 'ekyna_commerce.tax_group',
                'position' => 70,
            ])
            ->addEntry([
                'name'     => 'tax_rules',
                'resource' => 'ekyna_commerce.tax_rule',
                'position' => 71,
            ])
            ->addEntry([
                'name'     => 'taxes',
                'resource' => 'ekyna_commerce.tax',
                'position' => 72,
            ])
            ->addEntry([
                'name'     => 'accounting',
                'resource' => 'ekyna_commerce.accounting',
                'position' => 80,
            ])
            ->addEntry([
                'name'     => 'countries',
                'resource' => 'ekyna_commerce.country',
                'position' => 98,
            ])
            ->addEntry([
                'name'     => 'currencies',
                'resource' => 'ekyna_commerce.currency',
                'position' => 99,
            ]);
    }
}
