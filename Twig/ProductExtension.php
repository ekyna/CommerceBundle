<?php

namespace Ekyna\Bundle\CommerceBundle\Twig;

use Ekyna\Bundle\CommerceBundle\Service\ConstantHelper;
use Ekyna\Bundle\CommerceBundle\Model\ProductTypes;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Product\Model\ProductTypes as Types;

/**
 * Class ProductExtension
 * @package Ekyna\Bundle\CommerceBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * @var ConstantHelper
     */
    private $constantHelper;


    /**
     * Constructor.
     *
     * @param ConstantHelper $constantHelper
     */
    public function __construct(ConstantHelper $constantHelper)
    {
        $this->constantHelper = $constantHelper;
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('product_type_label', [$this->constantHelper, 'renderProductTypeLabel'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('product_type_badge', [$this->constantHelper, 'renderProductTypeBadge'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('get_product_types', [ProductTypes::class, 'getConstants']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getTests()
    {
        return array(
            new \Twig_SimpleTest('simple_product', function(ProductInterface $product) {
                return $product->getType() === Types::TYPE_SIMPLE;
            }),
            new \Twig_SimpleTest('variable_product', function(ProductInterface $product) {
                return $product->getType() === Types::TYPE_VARIABLE;
            }),
            new \Twig_SimpleTest('variant_product', function(ProductInterface $product) {
                return $product->getType() === Types::TYPE_VARIANT;
            }),
            new \Twig_SimpleTest('bundle_product', function(ProductInterface $product) {
                return $product->getType() === Types::TYPE_BUNDLE;
            }),
            new \Twig_SimpleTest('configurable_product', function(ProductInterface $product) {
                return $product->getType() === Types::TYPE_CONFIGURABLE;
            }),
        );
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'ekyna_commerce_product';
    }
}
