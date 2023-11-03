<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection;

use DateTime;
use Ekyna\Bundle\CommerceBundle\Model\DocumentDesign;
use Ekyna\Bundle\CommerceBundle\Model\Genders;
use Ekyna\Component\Commerce\Common\Context\Context;
use Ekyna\Component\Commerce\Common\Locking\LockChecker;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Features;
use Ekyna\Component\Commerce\Pricing\Model\VatDisplayModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Exception;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;

/**
 * Class Configuration
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('ekyna_commerce');

        $root = $builder->getRootNode();

        // Config
        $this->addDefaultSection($root);
        $this->addAccountingSection($root);
        $this->addCacheSection($root);
        $this->addClassSection($root);
        $this->addDocumentSection($root);
        $this->addFeatureSection($root);
        $this->addPricingSection($root);
        $this->addStockSection($root);
        $this->addSubjectSection($root);
        $this->addTemplateSection($root);
        $this->addWidgetSection($root);

        return $builder;
    }

    private function addDefaultSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('default')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('company_logo')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('country')
                            ->cannotBeEmpty()
                            ->defaultValue('US')
                            ->validate()
                            ->ifNotInArray(array_keys(Countries::getNames()))
                                ->thenInvalid('Invalid default country %s')
                            ->end()
                        ->end()
                        ->scalarNode('currency')
                            ->cannotBeEmpty()
                            ->defaultValue('USD')
                            ->validate()
                            ->ifNotInArray(array_keys(Currencies::getNames()))
                                ->thenInvalid('Invalid default currency %s')
                            ->end()
                        ->end()
                        ->scalarNode('vat_display_mode')
                            ->cannotBeEmpty()
                            ->defaultValue(VatDisplayModes::MODE_ATI)
                            ->validate()
                            ->ifNotInArray(VatDisplayModes::getModes())
                                ->thenInvalid('Invalid VAT display mode %s')
                            ->end()
                        ->end()
                        ->arrayNode('expiration')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('cart')
                                    ->cannotBeEmpty()
                                    ->defaultValue('+1 month')
                                ->end()
                                ->scalarNode('quote')
                                    ->cannotBeEmpty()
                                    ->defaultValue('+2 months')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('fraud')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode('threshold')
                                    ->defaultValue(10)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('notify')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('administrators')
                                    ->defaultTrue()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('shipment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('label_retention')
                                    ->defaultValue('6 months')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('locking')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('start')
                                    ->defaultValue('first day of last month')
                                    ->validate()
                                        ->ifTrue(function (string $input) {
                                            try {
                                                LockChecker::createDate($input, 'lock start');
                                            } catch (LogicException $e) {
                                                return true;
                                            }
                                            return false;
                                        })
                                        ->thenInvalid("Invalid 'lock start' date '%s'.")
                                    ->end()
                                ->end()
                                ->scalarNode('end')
                                    ->defaultValue('last day of last month')
                                    ->validate()
                                        ->ifTrue(function (string $input) {
                                            try {
                                                LockChecker::createDate($input, 'lock end');
                                            } catch (LogicException $e) {
                                                return true;
                                            }
                                            return false;
                                        })
                                        ->thenInvalid("Invalid 'lock end' date '%s'.")
                                    ->end()
                                ->end()
                                ->scalarNode('since')
                                    ->defaultValue('first day of this month')
                                    ->validate()
                                        ->ifTrue(function (string $input) {
                                            try {
                                                LockChecker::createDate($input, 'lock since');
                                            } catch (LogicException $e) {
                                                return true;
                                            }
                                            return false;
                                        })
                                        ->thenInvalid("Invalid 'lock since' date '%s'.")
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addCacheSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('countries')
                            //->addDefaultsIfNotSet()
                            ->defaultValue(['US'])
                            ->scalarPrototype()->end()
                            ->validate()
                                ->ifTrue(function($codes) {
                                    $valid = array_keys(Currencies::getNames());
                                    foreach ($codes as $code) {
                                        if (in_array($code, $valid, true)) {
                                            return true;
                                        }
                                    }

                                    return false;
                                })
                                ->thenInvalid('Invalid default country %s')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addClassSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('class')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('context')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue(Context::class)
                        ->end()
                        ->scalarNode('genders')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue(Genders::class)
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addAccountingSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('accounting')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_customer')->defaultValue('10000000')->end()
                        ->booleanNode('total_as_payment')->defaultFalse()->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addDocumentSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('document')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('design_class')->defaultValue(DocumentDesign::class)->end()
                        ->scalarNode('primary_color')->defaultValue('#999999')->end()
                        ->scalarNode('secondary_color')->defaultValue('#dddddd')->end()
                        ->booleanNode('shipment_remaining_date')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addFeatureSection(ArrayNodeDefinition $node): void
    {
        // Must be kept in sync with:
        /** @see \Ekyna\Component\Commerce\Features */
        $node
            ->children()
                ->arrayNode('feature')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode(Features::BIRTHDAY)
                            ->canBeEnabled()
                        ->end()
                        ->arrayNode(Features::NEWSLETTER)
                            ->canBeEnabled()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('mailchimp')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('api_key')->defaultNull()->end()
                                    ->end()
                                ->end()
                                ->arrayNode('sendinblue')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('api_key')->defaultNull()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(Features::COUPON)
                            ->canBeEnabled()
                        ->end()
                        ->arrayNode(Features::LOYALTY)
                            ->canBeEnabled()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode('credit_rate')
                                    ->min(0)
                                    ->defaultValue(1)
                                ->end()
                                ->arrayNode('credit')
                                    ->defaultValue([
                                        'birthday'   => 0,
                                        'newsletter' => 0,
                                        'review'     => 0,
                                    ])
                                    ->useAttributeAsKey('name')
                                    ->prototype('integer')->min(0)->end()
                                    ->validate()
                                        ->ifTrue(function($value) {
                                            foreach (array_keys($value) as $key) {
                                                if (!in_array($key, ['birthday', 'newsletter', 'review'], true)) {
                                                    return true;
                                                }
                                            }
                                            return false;
                                        })
                                        ->thenInvalid('Invalid loyalty credit config.')
                                    ->end()
                                ->end()
                                ->arrayNode('coupons')
                                    ->defaultValue([])
                                    ->useAttributeAsKey('name')
                                    ->prototype('array')
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->scalarNode('mode')
                                                ->defaultValue(AdjustmentModes::MODE_PERCENT)
                                                ->validate()
                                                    ->ifNotInArray([AdjustmentModes::MODE_PERCENT, AdjustmentModes::MODE_FLAT])
                                                    ->thenInvalid('Invalid loyalty coupon mode.')
                                                ->end()
                                            ->end()
                                            ->integerNode('amount')
                                                ->isRequired()
                                                ->validate()
                                                    ->ifTrue(function($value) {
                                                        return 0 >= $value;
                                                    })
                                                    ->thenInvalid('Invalid loyalty coupon amount.')
                                                ->end()
                                            ->end()
                                            ->scalarNode('period')
                                                ->defaultValue('+2 months')
                                                ->validate()
                                                    ->ifTrue(function($value) {
                                                        try {
                                                            new DateTime($value);
                                                        } catch(Exception $e) {
                                                            return true;
                                                        }
                                                        return false;
                                                    })
                                                    ->thenInvalid('Invalid loyalty coupon period.')
                                                ->end()
                                            ->end()
                                            ->booleanNode('final')
                                                ->defaultValue(false)
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(Features::SUPPORT)
                            ->canBeEnabled()
                        ->end()
                        ->arrayNode(Features::CUSTOMER_GRAPHIC)
                            ->canBeEnabled()
                        ->end()
                        ->arrayNode(Features::CUSTOMER_CONTACT)
                            ->canBeEnabled()
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('account')
                                    ->defaultValue(false)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(Features::RESUPPLY_ALERT)
                            ->canBeEnabled()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addPricingSection(ArrayNodeDefinition $node): void
    {
        $apiDefaults = ['enabled' => false, 'access_key' => null];
        $wsDefaults  = ['enabled' => false];

        $node
            ->children()
                ->arrayNode('pricing')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('provider')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('europa')
                                    ->info('To use https://europa.eu VIES web service')
                                    ->addDefaultsIfNotSet()
                                    ->treatFalseLike($wsDefaults)
                                    ->treatNullLike($wsDefaults)
                                    ->treatTrueLike(['enabled' => true])
                                    ->children()
                                        ->booleanNode('enabled')
                                            ->defaultFalse()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('vat_layer')
                                    ->info('To use https://vatlayer.com API')
                                    ->addDefaultsIfNotSet()
                                    ->treatFalseLike($apiDefaults)
                                    ->treatNullLike($apiDefaults)
                                    ->children()
                                        ->booleanNode('enabled')
                                            ->defaultFalse()
                                        ->end()
                                        ->scalarNode('access_key')
                                            ->defaultNull()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('vat_api')
                                    ->info('To use https://vatapi.com API')
                                    ->addDefaultsIfNotSet()
                                    ->treatFalseLike($apiDefaults)
                                    ->treatNullLike($apiDefaults)
                                    ->children()
                                        ->booleanNode('enabled')
                                            ->defaultFalse()
                                        ->end()
                                        ->scalarNode('access_key')
                                            ->defaultNull()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addStockSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('stock')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('availability')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode('in_stock_limit')->defaultValue(100)->end()
                            ->end()
                        ->end()
                        ->arrayNode('subject_default')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('stock_mode')->defaultValue(StockSubjectModes::MODE_AUTO)->cannotBeEmpty()->end()
                                ->integerNode('stock_floor')->defaultValue(0)->end()
                                ->integerNode('replenishment_time')->defaultValue(2)->end()
                                ->integerNode('minimum_order_quantity')->defaultValue(1)->end()
                                ->booleanNode('quote_only')->defaultFalse()->end()
                                ->booleanNode('end_of_life')->defaultFalse()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addSubjectSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('subject')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('add_to_cart')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('class')->defaultValue('')->end()
                                ->scalarNode('icon')->defaultNull()->end()
                                ->arrayNode('attr')
                                    ->useAttributeAsKey('name')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('add_to_cart')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('label')->defaultValue('button.add_to_cart')->end()
                                        ->scalarNode('domain')->defaultValue('EkynaCommerce')->end()
                                        ->scalarNode('class')->defaultValue('add_to_cart')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('pre_order')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('label')->defaultValue('button.pre_order')->end()
                                        ->scalarNode('domain')->defaultValue('EkynaCommerce')->end()
                                        ->scalarNode('class')->defaultValue('pre_order')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('resupply_alert')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('label')->defaultValue('button.resupply_alert')->end()
                                        ->scalarNode('domain')->defaultValue('EkynaCommerce')->end()
                                        ->scalarNode('class')->defaultValue('resupply_alert')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('out_of_stock')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('label')->defaultValue('stock_subject.availability.long.out_of_stock')->end()
                                        ->scalarNode('domain')->defaultValue('EkynaCommerce')->end()
                                        ->scalarNode('class')->defaultValue('out_of_stock')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('quote_only')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('label')->defaultValue('stock_subject.availability.long.quote_only')->end()
                                        ->scalarNode('domain')->defaultValue('EkynaCommerce')->end()
                                        ->scalarNode('class')->defaultValue('quote_only')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('end_of_life')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('label')->defaultValue('stock_subject.availability.long.end_of_life')->end()
                                        ->scalarNode('domain')->defaultValue('EkynaCommerce')->end()
                                        ->scalarNode('class')->defaultValue('end_of_life')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addTemplateSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('template')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('shipment_price_list')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('@EkynaCommerce/Admin/ShipmentPrice/list.html.twig')
                        ->end()
                        ->scalarNode('stock_unit_list')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('@EkynaCommerce/Admin/Stock/stock_units.html.twig')
                        ->end()
                        ->scalarNode('stock_assignment_list')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('@EkynaCommerce/Admin/Stock/stock_assignments.html.twig')
                        ->end()
                        ->scalarNode('subject_stock_list')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('@EkynaCommerce/Admin/Stock/subjects_stock.html.twig')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addWidgetSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('widget')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('data')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('cart')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('id')->defaultValue('cart-widget')->end()
                                        ->scalarNode('label')->defaultValue('widget.cart.title')->end()
                                        ->scalarNode('title')->defaultValue('widget.cart.title')->end()
                                        ->scalarNode('trans_domain')->defaultValue('EkynaCommerce')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('context')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('id')->defaultValue('context-widget')->end()
                                        ->scalarNode('label')->defaultValue('<span class="country-flag %%1$s" title="%%2$s"></span><span class="currency">%%3$s</span><span class="locale">%%4$s</span>')->end()
                                        ->scalarNode('title')->defaultValue('widget.context.title')->end()
                                        ->scalarNode('trans_domain')->defaultValue('EkynaCommerce')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('customer')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('id')->defaultValue('customer-widget')->end()
                                        ->scalarNode('label')->defaultValue('widget.customer.title')->end()
                                        ->scalarNode('title')->defaultValue('widget.customer.title')->end()
                                        ->scalarNode('trans_domain')->defaultValue('EkynaCommerce')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('currency')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('id')->defaultValue('currency-widget')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('template')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('widget')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->defaultValue('@EkynaCommerce/Js/widget.html.twig')
                                ->end()
                                ->scalarNode('cart')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->defaultValue('@EkynaCommerce/Widget/cart.html.twig')
                                ->end()
                                ->scalarNode('context')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->defaultValue('@EkynaCommerce/Widget/context.html.twig')
                                ->end()
                                ->scalarNode('currency')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->defaultValue('@EkynaCommerce/Widget/currency.html.twig')
                                ->end()
                                ->scalarNode('customer')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->defaultValue('@EkynaCommerce/Widget/customer.html.twig')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
