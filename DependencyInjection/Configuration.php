<?php

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ekyna_commerce');

        $this->addDefaultSection($rootNode);
        $this->addPoolsSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Adds `pools` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addDefaultSection(ArrayNodeDefinition $node)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $node
            ->children()
                ->arrayNode('default')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('countries')
                            ->defaultValue(['US'])
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('currencies')
                            ->defaultValue(['USD'])
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Adds `pools` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addPoolsSection(ArrayNodeDefinition $node)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $node
            ->children()
                ->arrayNode('pools')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('attribute')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue('EkynaCommerceBundle:Admin/Attribute')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Product\Entity\Attribute')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\AttributeType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\AttributeType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.attribute_group')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('attribute_group')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    'show.html'  => 'EkynaCommerceBundle:Admin/AttributeGroup:show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Product\Entity\AttributeGroup')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\AttributeGroupType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\AttributeGroupType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('attribute_set')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    'show.html'  => 'EkynaCommerceBundle:Admin/AttributeSet:show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Product\Entity\AttributeSet')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\AttributeSetType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\AttributeSetType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('cart')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Cart\Entity\Cart')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\CommerceBundle\Repository\CartRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\CartType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\CartType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('country')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Common\Entity\Country')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\CountryController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\CountryType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\CountryType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('currency')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Common\Entity\Currency')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\CurrencyController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\CurrencyType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\CurrencyType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('customer_address')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue('EkynaCommerceBundle:Admin/CustomerAddress')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\CommerceBundle\Entity\CustomerAddress')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\CustomerAddressController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\CustomerAddressType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\CustomerAddressType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.customer')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('customer')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => 'EkynaCommerceBundle:Admin/Customer:_form.html',
                                    'show.html'  => 'EkynaCommerceBundle:Admin/Customer:show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\CommerceBundle\Entity\Customer')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\CustomerController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\CustomerType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\CustomerType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->defaultValue('Ekyna\Bundle\CommerceBundle\Event\CustomerEvent')->end()
                            ->end()
                        ->end()
                        ->arrayNode('customer_group')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Customer\Entity\CustomerGroup')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\CustomerGroupController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\CustomerGroupType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\CustomerGroupType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('order')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'             => 'EkynaCommerceBundle:Admin/Order:_form.html',
                                    'show.html'              => 'EkynaCommerceBundle:Admin/Order:show.html',
                                    'edit.html'              => 'EkynaCommerceBundle:Admin/Order:edit.html',
                                    'remove.html'            => 'EkynaCommerceBundle:Admin/Order:remove.html',
                                    'add_subject.html'       => 'EkynaCommerceBundle:Admin/OrderItem:add.html',
                                    'configure_subject.html' => 'EkynaCommerceBundle:Admin/OrderItem:configure.html',
                                    'new_item.html'          => 'EkynaCommerceBundle:Admin/OrderItem:new.html',
                                    'edit_item.html'         => 'EkynaCommerceBundle:Admin/OrderItem:edit.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\CommerceBundle\Entity\Order')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\OrderController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\OrderType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\OrderType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->defaultValue('Ekyna\Bundle\CommerceBundle\Event\OrderEvent')->end()
                            ->end()
                        ->end()
                        ->arrayNode('product')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue('EkynaCommerceBundle:Admin/Product')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Product\Entity\Product')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\ProductController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\CommerceBundle\Repository\ProductRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\ProductType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\ProductType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->defaultValue('Ekyna\Bundle\CommerceBundle\Event\ProductEvent')->end()
                            ->end()
                        ->end()
                        ->arrayNode('tax')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => 'EkynaCommerceBundle:Admin/Tax:_form.html',
                                    'show.html'  => 'EkynaCommerceBundle:Admin/Tax:show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Pricing\Entity\Tax')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\TaxController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\TaxType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\TaxType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('tax_group')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => 'EkynaCommerceBundle:Admin/TaxGroup:_form.html',
                                    'show.html'  => 'EkynaCommerceBundle:Admin/TaxGroup:show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Pricing\Entity\TaxGroup')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\TaxGroupController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\TaxGroupType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\TaxGroupType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('tax_rule')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => 'EkynaCommerceBundle:Admin/TaxRule:_form.html',
                                    'show.html'  => 'EkynaCommerceBundle:Admin/TaxRule:show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Pricing\Entity\TaxRule')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\TaxRuleController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\CommerceBundle\Repository\TaxRuleRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\TaxRuleType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\TaxRuleType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
