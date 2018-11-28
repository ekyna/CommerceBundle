<?php

namespace Acme\ProductBundle\DependencyInjection;

use Acme\Product\Bridge\DoctrineBridge;
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
        // Doctrine ORM
        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'mappings' => [
                    'AcmeProduct' => DoctrineBridge::getORMMappingConfiguration(),
                ],
            ],
        ]);

        // Ekyna Resource
        $container->prependExtensionConfig('ekyna_resource', [
            'resources' => [
                'acme_product' => [
                    'product'    => [
                        'entity'     => 'Acme\Product\Entity\Product',
                        'repository' => 'Acme\Product\Repository\ProductRepository',
                        'form'       => 'Acme\ProductBundle\Form\Type\ProductType',
                        'table'      => 'Acme\ProductBundle\Table\Type\ProductType',
                        'event' => [
                            'priority' => 1024,
                        ],
                        'templates'  => [
                            'show.html' => '@AcmeProduct/Admin/Product/show.html',
                        ],
                    ],
                    'stock_unit' => [
                        'entity'     => 'Acme\Product\Entity\StockUnit',
                        'repository' => 'Acme\Product\Repository\StockUnitRepository',
                        'form'       => 'Acme\ProductBundle\Form\Type\StockUnitType',
                        'event' => [
                            'priority' => 1280
                        ],
                        'templates'  => 'AcmeProductBundle:Admin/StockUnit',
                        'parent'     => 'acme_product.product',
                    ],
                ],
            ],
        ]);

        // FOS Elastica
        $elasticConfig = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/config/prepend/FOSElasticaBundle.yml'));
        $container->prependExtensionConfig('fos_elastica', $elasticConfig['fos_elastica']);
    }
}
