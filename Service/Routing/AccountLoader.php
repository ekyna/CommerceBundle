<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class AccountLoader
 * @package Ekyna\Bundle\CommerceBundle\Service\Routing
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AccountLoader extends Loader
{
    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @var array
     */
    private $config;


    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = array_replace([
            'support' => true,
        ], $config);
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "commerce account" routes loader twice.');
        }

        $collection = new RouteCollection();

        if ($this->config['support']) {
            $routes = $this->import('@EkynaCommerceBundle/Resources/config/routing/front/account/ticket.yml', 'yaml');
            $routes->addPrefix('/tickets');

            $collection->addCollection($routes);
        }

        $this->loaded = true;

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'commerce_account' === $type;
    }
}
