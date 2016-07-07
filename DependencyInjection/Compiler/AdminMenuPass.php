<?php

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler;

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

        $pool = $container->getDefinition('ekyna_admin.menu.pool');

        $pool->addMethodCall('createGroup', [[
            'name'     => 'commerce',
            'label'    => 'ekyna_commerce.label',
            'icon'     => 'file',
            'position' => 30,
        ]]);

        // Orders
        $pool->addMethodCall('createEntry', ['commerce', [
            'name'     => 'orders',
            'route'    => 'ekyna_commerce_order_admin_home',
            'label'    => 'ekyna_commerce.order.label.plural',
            'resource' => 'ekyna_commerce_order',
            'position' => 1,
        ]]);

        // Customers
        $pool->addMethodCall('createEntry', ['commerce', [
            'name'     => 'customer_groups',
            'route'    => 'ekyna_commerce_customer_group_admin_home',
            'label'    => 'ekyna_commerce.customer_group.label.plural',
            'resource' => 'ekyna_commerce_customer_group',
            'position' => 10,
        ]]);
        $pool->addMethodCall('createEntry', ['commerce', [
            'name'     => 'customers',
            'route'    => 'ekyna_commerce_customer_admin_home',
            'label'    => 'ekyna_commerce.customer.label.plural',
            'resource' => 'ekyna_commerce_customer',
            'position' => 11,
        ]]);

        // Tax groups / Tax rules / Taxes
        $pool->addMethodCall('createEntry', ['commerce', [
            'name'     => 'tax_group',
            'route'    => 'ekyna_commerce_tax_group_admin_home',
            'label'    => 'ekyna_commerce.tax_group.label.plural',
            'resource' => 'ekyna_commerce_tax_group',
            'position' => 70,
        ]]);
        $pool->addMethodCall('createEntry', ['commerce', [
            'name'     => 'tax_rule',
            'route'    => 'ekyna_commerce_tax_rule_admin_home',
            'label'    => 'ekyna_commerce.tax_rule.label.plural',
            'resource' => 'ekyna_commerce_tax_rule',
            'position' => 71,
        ]]);
        $pool->addMethodCall('createEntry', ['commerce', [
            'name'     => 'taxes',
            'route'    => 'ekyna_commerce_tax_admin_home',
            'label'    => 'ekyna_commerce.tax.label.plural',
            'resource' => 'ekyna_commerce_tax',
            'position' => 72,
        ]]);

        // Countries / Currencies
        $pool->addMethodCall('createEntry', ['commerce', [
            'name'     => 'countries',
            'route'    => 'ekyna_commerce_country_admin_home',
            'label'    => 'ekyna_commerce.country.label.plural',
            'resource' => 'ekyna_commerce_country',
            'position' => 98,
        ]]);
        $pool->addMethodCall('createEntry', ['commerce', [
            'name'     => 'currencies',
            'route'    => 'ekyna_commerce_currency_admin_home',
            'label'    => 'ekyna_commerce.currency.label.plural',
            'resource' => 'ekyna_commerce_currency',
            'position' => 99,
        ]]);
    }
}
