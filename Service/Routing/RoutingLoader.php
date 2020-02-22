<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Routing;

use Ekyna\Component\Commerce\Features;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RoutingLoader
 * @package Ekyna\Bundle\CommerceBundle\Service\Routing
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class RoutingLoader extends Loader
{
    /**
     * @var Features
     */
    private $features;

    /**
     * @var bool
     */
    private $loaded = false;


    /**
     * Constructor.
     *
     * @param Features $feature
     */
    public function __construct(Features $feature)
    {
        $this->features = $feature;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "commerce account" routes loader twice.');
        }

        $this->loaded = true;

        $collection = new RouteCollection();

        $accountCollection = new RouteCollection();
        $accountCollection->addPrefix('/my-account');

        if ($this->features->isEnabled(Features::LOYALTY)) {
            $routes = $this->import('@EkynaCommerceBundle/Resources/config/routing/front/account/loyalty.yml', 'yaml');
            $routes->addPrefix('/loyalty');

            $accountCollection->addCollection($routes);
        }

        if ($this->features->isEnabled(Features::NEWSLETTER)) {
            $routes = $this->import('@EkynaCommerceBundle/Resources/config/routing/front/newsletter.yml', 'yaml');
            $routes->addPrefix('/newsletter');
            $collection->addCollection($routes);

            $routes = $this->import('@EkynaCommerceBundle/Resources/config/routing/front/account/newsletter.yml', 'yaml');
            $routes->addPrefix('/newsletter');
            $accountCollection->addCollection($routes);
        }

        if ($this->features->isEnabled(Features::SUPPORT)) {
            $routes = $this->import('@EkynaCommerceBundle/Resources/config/routing/front/account/ticket.yml', 'yaml');
            $routes->addPrefix('/tickets');

            $accountCollection->addCollection($routes);
        }

        if (0 < $accountCollection->count()) {
            $collection->addCollection($accountCollection);
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'commerce_routing' === $type;
    }
}
