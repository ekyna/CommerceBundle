<?php

namespace Acme\ProductBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AcmeProductExtension
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AcmeProductExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('ekyna_resource', [
            'resources' => [
                'acme_product' => [
                    'product'    => [
                        'entity'     => 'Acme\ProductBundle\Entity\Product',
                        'repository' => 'Acme\ProductBundle\Repository\ProductRepository',
                        'form'       => 'Acme\ProductBundle\Form\Type\ProductType',
                        'table'      => 'Acme\ProductBundle\Table\Type\ProductType',
                        'templates'  => [
                            'show.html' => 'AcmeProductBundle:Admin/Product:show.html',
                        ],
                    ],
                    'stock_unit' => [
                        'entity'     => 'Acme\ProductBundle\Entity\StockUnit',
                        'repository' => 'Acme\ProductBundle\Repository\StockUnitRepository',
                        'form'       => 'Acme\ProductBundle\Form\Type\StockUnitType',
                        'templates'  => 'AcmeProductBundle:Admin/StockUnit',
                        'parent'     => 'acme_product.product',
                    ],
                ],
            ],
        ]);

        $elasticConfig = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/config/prepend/FOSElasticaBundle.yml'));
        $container->prependExtensionConfig('fos_elastica', $elasticConfig['fos_elastica']);
    }
}
