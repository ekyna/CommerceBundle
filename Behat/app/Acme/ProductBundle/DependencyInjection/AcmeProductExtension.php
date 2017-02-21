<?php

namespace Acme\ProductBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;

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
                    'product'            => [
                        'entity' => 'Acme\ProductBundle\Entity\Product',
                        'repository' => 'Acme\ProductBundle\Repository\ProductRepository',
                    ],
                    'stock_unit' => [
                        'entity'     => 'Acme\ProductBundle\Entity\StockUnit',
                        'repository' => 'Acme\ProductBundle\Repository\StockUnitRepository',
                    ],
                ],
            ],
        ]);
    }
}
