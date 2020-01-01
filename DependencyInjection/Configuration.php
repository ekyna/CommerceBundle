<?php

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection;

use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Features;
use Ekyna\Component\Commerce\Pricing\Model\VatDisplayModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Intl\Intl;

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
        $this->addAccountingSection($rootNode);
        $this->addCacheSection($rootNode);
        $this->addDocumentSection($rootNode);
        $this->addFeatureSection($rootNode);
        $this->addPricingSection($rootNode);
        $this->addStockSection($rootNode);
        $this->addPoolsSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Adds `default` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addDefaultSection(ArrayNodeDefinition $node)
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
                            ->ifNotInArray(array_keys(Intl::getRegionBundle()->getCountryNames()))
                                ->thenInvalid('Invalid default country %s')
                            ->end()
                        ->end()
                        ->scalarNode('currency')
                            ->cannotBeEmpty()
                            ->defaultValue('USD')
                            ->validate()
                            ->ifNotInArray(array_keys(Intl::getCurrencyBundle()->getCurrencyNames()))
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
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Adds `cache` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addCacheSection(ArrayNodeDefinition $node)
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
                                    $valid = array_keys(Intl::getCurrencyBundle()->getCurrencyNames());
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

    /**
     * Adds `accounting` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addAccountingSection(ArrayNodeDefinition $node)
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

    /**
     * Adds `document` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addDocumentSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('document')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('row_height')->defaultValue(27)->end()
                        ->integerNode('row_desc_height')->defaultValue(47)->end()
                        ->integerNode('page_height')->defaultValue(1399)->end()
                        ->integerNode('header_height')->defaultValue(370)->end()
                        ->integerNode('title_height')->defaultValue(130)->end()
                        ->integerNode('footer_height')->defaultValue(91)->end()
                        ->booleanNode('shipment_remaining_date')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Adds `feature` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addFeatureSection(ArrayNodeDefinition $node)
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
                        ->end()
                        ->arrayNode(Features::COUPON)
                            ->canBeEnabled()
                        ->end()
                        ->arrayNode(Features::LOYALTY)
                            ->canBeEnabled()
                            ->children()
                                ->integerNode('credit_rate')
                                    ->min(0)
                                    ->defaultValue(1)
                                ->end()
                                ->arrayNode('credit')
                                    ->useAttributeAsKey('name')
                                    ->prototype('integer')->min(0)->end()
                                    ->validate()
                                        ->ifTrue(function($value) {
                                            foreach (array_keys($value) as $key) {
                                                if (!in_array($key, [Features::BIRTHDAY, Features::NEWSLETTER], true)) {
                                                    return true;
                                                }
                                            }
                                            return false;
                                        })
                                        ->thenInvalid('Invalid loyalty credit config.')
                                    ->end()
                                ->end()
                                ->arrayNode('coupons')
                                    ->useAttributeAsKey('name')
                                    ->prototype('array')
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
                                                            new \DateTime($value);
                                                        } catch(\Exception $e) {
                                                            return true;
                                                        }
                                                        return false;
                                                    })
                                                    ->thenInvalid('Invalid loyalty coupon period.')
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(Features::SUPPORT)
                            ->canBeEnabled()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Adds `pricing` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addPricingSection(ArrayNodeDefinition $node)
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
                                    ->treatTrueLike(array('enabled' => true))
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

    /**
     * Adds `stock` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addStockSection(ArrayNodeDefinition $node)
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

    /**
     * Adds `pools` section.
     *
     * @param ArrayNodeDefinition $node
     *
     * @TODO Split
     */
    private function addPoolsSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('pools')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('accounting')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/Accounting/_form.html',
                                    'show.html'   => '@EkynaCommerce/Admin/Accounting/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Accounting\Entity\Accounting')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\AccountingController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\AccountingRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Accounting\AccountingType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\AccountingType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('cart')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'     => '@EkynaCommerce/Admin/Cart/_form.html',
                                    'list.html'      => '@EkynaCommerce/Admin/Cart/list.html',
                                    'show.html'      => '@EkynaCommerce/Admin/Cart/show.html',
                                    'edit.html'      => '@EkynaCommerce/Admin/Cart/edit.html',
                                    'remove.html'    => '@EkynaCommerce/Admin/Cart/remove.html',
                                    'transform.html' => '@EkynaCommerce/Admin/Cart/transform.html',
                                    'duplicate.html' => '@EkynaCommerce/Admin/Cart/duplicate.html',
                                    'notify.html'    => '@EkynaCommerce/Admin/Cart/notify.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Cart\Entity\Cart')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SaleController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\CartRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Cart\CartType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\CartType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('cart_address')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Cart\Entity\CartAddress')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.cart')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Cart\CartAddressType')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.address')->end()
                            ->end()
                        ->end()
                        ->arrayNode('cart_attachment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/Common/Attachment/_form.html',
                                    'new.html'    => '@EkynaCommerce/Admin/Common/Attachment/new.html',
                                    'edit.html'   => '@EkynaCommerce/Admin/Common/Attachment/edit.html',
                                    'remove.html' => '@EkynaCommerce/Admin/Common/Attachment/remove.html',
                                ])->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\AttachmentController')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Cart\Entity\CartAttachment')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Cart\CartAttachmentType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.cart')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.attachment')->end()
                            ->end()
                        ->end()
                        ->arrayNode('cart_item')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'     => '@EkynaCommerce/Admin/Common/Item/_form.html',
                                    'add.html'       => '@EkynaCommerce/Admin/Common/Item/add.html',
                                    'new.html'       => '@EkynaCommerce/Admin/Common/Item/new.html',
                                    'configure.html' => '@EkynaCommerce/Admin/Common/Item/configure.html',
                                    'edit.html'      => '@EkynaCommerce/Admin/Common/Item/edit.html',
                                    'remove.html'    => '@EkynaCommerce/Admin/Common/Item/remove.html',
                                ])->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SaleItemController')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Cart\Entity\CartItem')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Cart\CartItemType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.cart')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.sale_item')->end()
                            ->end()
                        ->end()
                        ->arrayNode('cart_item_adjustment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/Common/Adjustment/_form.html',
                                    'new.html'    => '@EkynaCommerce/Admin/Common/Adjustment/new.html',
                                    'edit.html'   => '@EkynaCommerce/Admin/Common/Adjustment/edit.html',
                                    'remove.html' => '@EkynaCommerce/Admin/Common/Adjustment/remove.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Cart\Entity\CartItemAdjustment')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SaleItemAdjustmentController')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Cart\CartItemAdjustmentType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.cart_item')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.adjustment')->end()
                            ->end()
                        ->end()
                        ->arrayNode('cart_adjustment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/Common/Adjustment/_form.html',
                                    'new.html'    => '@EkynaCommerce/Admin/Common/Adjustment/new.html',
                                    'edit.html'   => '@EkynaCommerce/Admin/Common/Adjustment/edit.html',
                                    'remove.html' => '@EkynaCommerce/Admin/Common/Adjustment/remove.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Cart\Entity\CartAdjustment')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SaleAdjustmentController')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Cart\CartAdjustmentType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.cart')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.adjustment')->end()
                            ->end()
                        ->end()
                        ->arrayNode('cart_payment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/Common/Payment/_form.html',
                                    'new.html'    => '@EkynaCommerce/Admin/Common/Payment/new.html',
                                    'edit.html'   => '@EkynaCommerce/Admin/Common/Payment/edit.html',
                                    'remove.html' => '@EkynaCommerce/Admin/Common/Payment/remove.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Cart\Entity\CartPayment')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\CartPaymentRepository')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SalePaymentController')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Cart\CartPaymentType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.cart')->end()
                                ->scalarNode('event')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.payment')->end()
                            ->end()
                        ->end()
                        ->arrayNode('country')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/Country/_form.html',
                                    'show.html'  => '@EkynaCommerce/Admin/Country/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Common\Entity\Country')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\CountryController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\CountryRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Common\CountryType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\CountryType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('coupon')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/Coupon/_form.html',
                                    'show.html'  => '@EkynaCommerce/Admin/Coupon/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Common\Entity\Coupon')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\CouponRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Common\CouponType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\CouponType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('currency')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/Currency/_form.html',
                                    'show.html'  => '@EkynaCommerce/Admin/Currency/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Common\Entity\Currency')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\CurrencyController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\CurrencyRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Common\CurrencyType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\CurrencyType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('customer_address')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/CustomerAddress/_form.html',
                                    'edit.html'   => '@EkynaCommerce/Admin/CustomerAddress/edit.html',
                                    'import.html' => '@EkynaCommerce/Admin/CustomerAddress/import.html',
                                    'new.html'    => '@EkynaCommerce/Admin/CustomerAddress/new.html',
                                    'remove.html' => '@EkynaCommerce/Admin/CustomerAddress/remove.html',
                                    'show.html'   => '@EkynaCommerce/Admin/CustomerAddress/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Customer\Entity\CustomerAddress')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\CustomerAddressController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\CommerceBundle\Repository\CustomerAddressRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerAddressType')->end()
                                ->scalarNode('table')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.customer')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('customer')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'       => '@EkynaCommerce/Admin/Customer/_form.html',
                                    'list.html'        => '@EkynaCommerce/Admin/Customer/list.html',
                                    'show.html'        => '@EkynaCommerce/Admin/Customer/show.html',
                                    'balance.html'     => '@EkynaCommerce/Admin/Customer/balance.html',
                                    'export.html'      => '@EkynaCommerce/Admin/Customer/export.html',
                                    'create_user.html' => '@EkynaCommerce/Admin/Customer/create_user.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\CommerceBundle\Entity\Customer')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\CustomerController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\CommerceBundle\Repository\CustomerRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\CustomerType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('customer_group')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/CustomerGroup/_form.html',
                                    'show.html'  => '@EkynaCommerce/Admin/CustomerGroup/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Customer\Entity\CustomerGroup')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\CustomerGroupController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\CustomerGroupRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Customer\CustomerGroupType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\CustomerGroupType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Customer\Entity\CustomerGroupTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['title'])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('notify_model')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/NotifyModel/_form.html',
                                    'show.html'  => '@EkynaCommerce/Admin/NotifyModel/show.html',
                                    'test.html'  => '@EkynaCommerce/Admin/NotifyModel/test.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\CommerceBundle\Entity\NotifyModel')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\NotifyModelController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Notify\NotifyModelType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\NotifyModelType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Bundle\CommerceBundle\Entity\NotifyModelTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['subject', 'message'])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('order')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'     => '@EkynaCommerce/Admin/Order/_form.html',
                                    'list.html'      => '@EkynaCommerce/Admin/Order/list.html',
                                    'show.html'      => '@EkynaCommerce/Admin/Order/show.html',
                                    'edit.html'      => '@EkynaCommerce/Admin/Order/edit.html',
                                    'remove.html'    => '@EkynaCommerce/Admin/Order/remove.html',
                                    'transform.html' => '@EkynaCommerce/Admin/Order/transform.html',
                                    'duplicate.html' => '@EkynaCommerce/Admin/Order/duplicate.html',
                                    'notify.html'    => '@EkynaCommerce/Admin/Order/notify.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\CommerceBundle\Entity\Order')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SaleController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\CommerceBundle\Repository\OrderRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\OrderType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_address')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Order\Entity\OrderAddress')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.order')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderAddressType')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.address')->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_attachment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/Common/Attachment/_form.html',
                                    'new.html'    => '@EkynaCommerce/Admin/Common/Attachment/new.html',
                                    'edit.html'   => '@EkynaCommerce/Admin/Common/Attachment/edit.html',
                                    'remove.html' => '@EkynaCommerce/Admin/Common/Attachment/remove.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Order\Entity\OrderAttachment')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\AttachmentController')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderAttachmentType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.order')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.attachment')->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_item')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'     => '@EkynaCommerce/Admin/Common/Item/_form.html',
                                    'add.html'       => '@EkynaCommerce/Admin/Common/Item/add.html',
                                    'new.html'       => '@EkynaCommerce/Admin/Common/Item/new.html',
                                    'configure.html' => '@EkynaCommerce/Admin/Common/Item/configure.html',
                                    'edit.html'      => '@EkynaCommerce/Admin/Common/Item/edit.html',
                                    'remove.html'    => '@EkynaCommerce/Admin/Common/Item/remove.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Order\Entity\OrderItem')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SaleItemController')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderItemType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.order')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.sale_item')->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_item_stock_assignment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Order\Entity\OrderItemStockAssignment')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.order_item')->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_item_adjustment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/Common/Adjustment/_form.html',
                                    'new.html'    => '@EkynaCommerce/Admin/Common/Adjustment/new.html',
                                    'edit.html'   => '@EkynaCommerce/Admin/Common/Adjustment/edit.html',
                                    'remove.html' => '@EkynaCommerce/Admin/Common/Adjustment/remove.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Order\Entity\OrderItemAdjustment')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SaleItemAdjustmentController')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderItemAdjustmentType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.order_item')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.adjustment')->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_adjustment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/Common/Adjustment/_form.html',
                                    'new.html'    => '@EkynaCommerce/Admin/Common/Adjustment/new.html',
                                    'edit.html'   => '@EkynaCommerce/Admin/Common/Adjustment/edit.html',
                                    'remove.html' => '@EkynaCommerce/Admin/Common/Adjustment/remove.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Order\Entity\OrderAdjustment')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SaleAdjustmentController')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderAdjustmentType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.order')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.adjustment')->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_payment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/Common/Payment/_form.html',
                                    'new.html'    => '@EkynaCommerce/Admin/Common/Payment/new.html',
                                    'edit.html'   => '@EkynaCommerce/Admin/Common/Payment/edit.html',
                                    'remove.html' => '@EkynaCommerce/Admin/Common/Payment/remove.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Order\Entity\OrderPayment')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\OrderPaymentRepository')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SalePaymentController')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderPaymentType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\OrderPaymentType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.order')->end()
                                ->scalarNode('event')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.payment')->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_shipment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/Common/Shipment/_form.html',
                                    'new.html'    => '@EkynaCommerce/Admin/Common/Shipment/new.html',
                                    'edit.html'   => '@EkynaCommerce/Admin/Common/Shipment/edit.html',
                                    'remove.html' => '@EkynaCommerce/Admin/Common/Shipment/remove.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Order\Entity\OrderShipment')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SaleShipmentController')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderShipmentType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\OrderShipmentType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.order')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.shipment')->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_shipment_item')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Order\Entity\OrderShipmentItem')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.order_shipment')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderShipmentItemType')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.sale_item')->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_shipment_parcel')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Order\Entity\OrderShipmentParcel')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.order_shipment')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderShipmentParcelType')->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_invoice')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/Common/Invoice/_form.html',
                                    'new.html'    => '@EkynaCommerce/Admin/Common/Invoice/new.html',
                                    'edit.html'   => '@EkynaCommerce/Admin/Common/Invoice/edit.html',
                                    'remove.html' => '@EkynaCommerce/Admin/Common/Invoice/remove.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Order\Entity\OrderInvoice')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\OrderInvoiceRepository')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SaleInvoiceController')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderInvoiceType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\OrderInvoiceType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.order')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.invoice')->end()
                            ->end()
                        ->end()
                        ->arrayNode('order_invoice_line')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Order\Entity\OrderInvoiceLine')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.order_invoice')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Order\OrderInvoiceLineType')->end()
                            ->end()
                        ->end()
                        ->arrayNode('payment_message')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Payment\Entity\PaymentMessage')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Payment\Entity\PaymentMessageTranslation')->end()
                                        ->arrayNode('fields')->prototype('scalar')->end()->defaultValue(['content'])->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('payment_method')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/PaymentMethod/_form.html',
                                    'new.html'   => '@EkynaCommerce/Admin/PaymentMethod/new.html',
                                    'show.html'  => '@EkynaCommerce/Admin/PaymentMethod/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\CommerceBundle\Entity\PaymentMethod')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\PaymentMethodController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\PaymentMethodRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentMethodType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\PaymentMethodType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Payment\Entity\PaymentMethodTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['title', 'description'])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('payment_term')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/PaymentTerm/_form.html',
                                    'show.html'  => '@EkynaCommerce/Admin/PaymentTerm/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Payment\Entity\PaymentTerm')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\PaymentTermRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentTermType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\PaymentTermType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Payment\Entity\PaymentTermTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['title', 'description'])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('relay_point')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Shipment\Entity\RelayPoint')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\RelayPointRepository')->end()
                            ->end()
                        ->end()
                        ->arrayNode('shipment_message')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Shipment\Entity\ShipmentMessage')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Shipment\Entity\ShipmentMessageTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')->prototype('scalar')->end()->defaultValue(['content'])->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('shipment_method')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/ShipmentMethod/_form.html',
                                    'show.html'  => '@EkynaCommerce/Admin/ShipmentMethod/show.html',
                                    'new.html'   => '@EkynaCommerce/Admin/ShipmentMethod/new.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\CommerceBundle\Entity\ShipmentMethod')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\ShipmentMethodController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\ShipmentMethodRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentMethodType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\ShipmentMethodType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Shipment\Entity\ShipmentMethodTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['title', 'description'])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('shipment_zone')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/ShipmentZone/_form.html',
                                    'show.html'  => '@EkynaCommerce/Admin/ShipmentZone/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Shipment\Entity\ShipmentZone')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\ShipmentZoneController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\ShipmentZoneRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentZoneType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\ShipmentZoneType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('shipment_rule')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/ShipmentRule/_form.html',
                                    'show.html'  => '@EkynaCommerce/Admin/ShipmentRule/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Shipment\Entity\ShipmentRule')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\ShipmentRuleRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentRuleType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\ShipmentRuleType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('shipment_price')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Shipment\Entity\ShipmentPrice')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\ShipmentPriceRepository')->end()
                            ->end()
                        ->end()
                        ->arrayNode('stock_adjustment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Stock\Entity\StockAdjustment')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Stock\StockAdjustmentType')->end()
                            ->end()
                        ->end()
                        ->arrayNode('supplier')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/Supplier/_form.html',
                                    'show.html'  => '@EkynaCommerce/Admin/Supplier/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Supplier\Entity\Supplier')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SupplierController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\SupplierType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('supplier_address')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Supplier\Entity\SupplierAddress')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.supplier')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.address')->end()
                            ->end()
                        ->end()
                        ->arrayNode('supplier_carrier')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/SupplierCarrier/_form.html',
                                    'show.html'   => '@EkynaCommerce/Admin/SupplierCarrier/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Supplier\Entity\SupplierCarrier')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierCarrierType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\SupplierCarrierType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('supplier_delivery')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue('@EkynaCommerce/Admin/SupplierDelivery')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Supplier\Entity\SupplierDelivery')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\SupplierDeliveryRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierDeliveryType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\SupplierDeliveryType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.supplier_order')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('supplier_delivery_item')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Supplier\Entity\SupplierDeliveryItem')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\SupplierDeliveryItemRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierDeliveryItemType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.supplier_delivery')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.sale_item')->end()
                            ->end()
                        ->end()
                        ->arrayNode('supplier_order')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/SupplierOrder/_form.html',
                                    'new.html'    => '@EkynaCommerce/Admin/SupplierOrder/new.html',
                                    'edit.html'   => '@EkynaCommerce/Admin/SupplierOrder/edit.html',
                                    'show.html'   => '@EkynaCommerce/Admin/SupplierOrder/show.html',
                                    'submit.html' => '@EkynaCommerce/Admin/SupplierOrder/submit.html',
                                    'notify.html' => '@EkynaCommerce/Admin/SupplierOrder/notify.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Supplier\Entity\SupplierOrder')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SupplierOrderController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\SupplierOrderRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierOrderType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\SupplierOrderType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('supplier_order_item')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Supplier\Entity\SupplierOrderItem')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\SupplierOrderItemRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierOrderItemType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.supplier_order')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.sale_item')->end()
                            ->end()
                        ->end()
                        ->arrayNode('supplier_order_attachment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/Common/Attachment/_form.html',
                                    'new.html'    => '@EkynaCommerce/Admin/Common/Attachment/new.html',
                                    'edit.html'   => '@EkynaCommerce/Admin/Common/Attachment/edit.html',
                                    'remove.html' => '@EkynaCommerce/Admin/Common/Attachment/remove.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Supplier\Entity\SupplierOrderAttachment')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\AttachmentController')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierOrderAttachmentType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.supplier_order')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.attachment')->end()
                            ->end()
                        ->end()
                        ->arrayNode('supplier_product')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue('@EkynaCommerce/Admin/SupplierProduct')->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Supplier\Entity\SupplierProduct')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\SupplierProductRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierProductType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\SupplierProductType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.supplier')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('supplier_template')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/SupplierTemplate/_form.html',
                                    'show.html'  => '@EkynaCommerce/Admin/SupplierTemplate/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Supplier\Entity\SupplierTemplate')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\SupplierTemplateRepository')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Supplier\SupplierTemplateType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\SupplierTemplateType')->end()
                                ->scalarNode('parent')->end()
                                ->arrayNode('translation')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Supplier\Entity\SupplierTemplateTranslation')->end()
                                        ->scalarNode('repository')->end()
                                        ->arrayNode('fields')
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['subject', 'message'])
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('quote')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'     => '@EkynaCommerce/Admin/Quote/_form.html',
                                    'list.html'      => '@EkynaCommerce/Admin/Quote/list.html',
                                    'show.html'      => '@EkynaCommerce/Admin/Quote/show.html',
                                    'edit.html'      => '@EkynaCommerce/Admin/Quote/edit.html',
                                    'remove.html'    => '@EkynaCommerce/Admin/Quote/remove.html',
                                    'transform.html' => '@EkynaCommerce/Admin/Quote/transform.html',
                                    'duplicate.html' => '@EkynaCommerce/Admin/Quote/duplicate.html',
                                    'notify.html'    => '@EkynaCommerce/Admin/Quote/notify.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\CommerceBundle\Entity\Quote')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SaleController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\QuoteRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\QuoteType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('quote_address')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Quote\Entity\QuoteAddress')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.quote')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteAddressType')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.address')->end()
                            ->end()
                        ->end()
                        ->arrayNode('quote_attachment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/Common/Attachment/_form.html',
                                    'new.html'    => '@EkynaCommerce/Admin/Common/Attachment/new.html',
                                    'edit.html'   => '@EkynaCommerce/Admin/Common/Attachment/edit.html',
                                    'remove.html' => '@EkynaCommerce/Admin/Common/Attachment/remove.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Quote\Entity\QuoteAttachment')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\AttachmentController')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteAttachmentType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.quote')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.attachment')->end()
                            ->end()
                        ->end()
                        ->arrayNode('quote_item')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'     => '@EkynaCommerce/Admin/Common/Item/_form.html',
                                    'add.html'       => '@EkynaCommerce/Admin/Common/Item/add.html',
                                    'new.html'       => '@EkynaCommerce/Admin/Common/Item/new.html',
                                    'configure.html' => '@EkynaCommerce/Admin/Common/Item/configure.html',
                                    'edit.html'      => '@EkynaCommerce/Admin/Common/Item/edit.html',
                                    'remove.html'    => '@EkynaCommerce/Admin/Common/Item/remove.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Quote\Entity\QuoteItem')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SaleItemController')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteItemType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.quote')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.sale_item')->end()
                            ->end()
                        ->end()
                        ->arrayNode('quote_item_adjustment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/Common/Adjustment/_form.html',
                                    'new.html'    => '@EkynaCommerce/Admin/Common/Adjustment/new.html',
                                    'edit.html'   => '@EkynaCommerce/Admin/Common/Adjustment/edit.html',
                                    'remove.html' => '@EkynaCommerce/Admin/Common/Adjustment/remove.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Quote\Entity\QuoteItemAdjustment')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SaleItemAdjustmentController')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteItemAdjustmentType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.quote_item')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.adjustment')->end()
                            ->end()
                        ->end()
                        ->arrayNode('quote_adjustment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/Common/Adjustment/_form.html',
                                    'new.html'    => '@EkynaCommerce/Admin/Common/Adjustment/new.html',
                                    'edit.html'   => '@EkynaCommerce/Admin/Common/Adjustment/edit.html',
                                    'remove.html' => '@EkynaCommerce/Admin/Common/Adjustment/remove.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Quote\Entity\QuoteAdjustment')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SaleAdjustmentController')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteAdjustmentType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.quote')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.adjustment')->end()
                            ->end()
                        ->end()
                        ->arrayNode('quote_payment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html'  => '@EkynaCommerce/Admin/Common/Payment/_form.html',
                                    'new.html'    => '@EkynaCommerce/Admin/Common/Payment/new.html',
                                    'edit.html'   => '@EkynaCommerce/Admin/Common/Payment/edit.html',
                                    'remove.html' => '@EkynaCommerce/Admin/Common/Payment/remove.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Quote\Entity\QuotePayment')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\QuotePaymentRepository')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\SalePaymentController')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuotePaymentType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.quote')->end()
                                ->scalarNode('event')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.payment')->end()
                            ->end()
                        ->end()
                        ->arrayNode('tax')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/Tax/_form.html',
                                    'show.html'  => '@EkynaCommerce/Admin/Tax/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Pricing\Entity\Tax')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\TaxType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\TaxType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('tax_group')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/TaxGroup/_form.html',
                                    'show.html'  => '@EkynaCommerce/Admin/TaxGroup/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Pricing\Entity\TaxGroup')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\TaxGroupController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\TaxGroupRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\TaxGroupType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\TaxGroupType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('tax_rule')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/TaxRule/_form.html',
                                    'show.html'  => '@EkynaCommerce/Admin/TaxRule/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Pricing\Entity\TaxRule')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\TaxRuleRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Pricing\TaxRuleType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\TaxRuleType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('ticket')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/Ticket/_form.html',
                                    'show.html'  => '@EkynaCommerce/Admin/Ticket/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\CommerceBundle\Entity\Ticket')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\TicketController')->end()
                                ->scalarNode('operator')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\TicketRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Support\TicketType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\TicketType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('ticket_message')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/TicketMessage/_form.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\CommerceBundle\Entity\TicketMessage')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\CommerceBundle\Repository\TicketMessageRepository')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\TicketMessageController')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Support\TicketMessageType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.ticket')->end()
                            ->end()
                        ->end()
                        ->arrayNode('ticket_attachment')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/TicketAttachment/_form.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Support\Entity\TicketAttachment')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\CommerceBundle\Controller\Admin\TicketAttachmentController')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Support\TicketAttachmentType')->end()
                                ->scalarNode('parent')->defaultValue('ekyna_commerce.ticket_message')->end()
                                ->scalarNode('trans_prefix')->defaultValue('ekyna_commerce.attachment')->end()
                            ->end()
                        ->end()
                        ->arrayNode('warehouse')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaCommerce/Admin/Warehouse/_form.html',
                                    'show.html'  => '@EkynaCommerce/Admin/Warehouse/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Component\Commerce\Stock\Entity\Warehouse')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\WarehouseRepository')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\CommerceBundle\Form\Type\Stock\WarehouseType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\CommerceBundle\Table\Type\WarehouseType')->end()
                                ->scalarNode('parent')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
