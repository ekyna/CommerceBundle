<?php

namespace Ekyna\Bundle\CommerceBundle\Service\Routing;

use Ekyna\Component\Commerce\Features;
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

        $collection = new RouteCollection();

        if ($this->features->isEnabled(Features::LOYALTY)) {
            $routes = $this->import('@EkynaCommerceBundle/Resources/config/routing/front/account/loyalty.yml', 'yaml');
            $routes->addPrefix('/loyalty');

            $collection->addCollection($routes);
        }

        if ($this->features->isEnabled(Features::NEWSLETTER)) {
            $routes = $this->import('@EkynaCommerceBundle/Resources/config/routing/front/account/newsletter.yml', 'yaml');
            $routes->addPrefix('/newsletter');

            $collection->addCollection($routes);
        }

        if ($this->features->isEnabled(Features::SUPPORT)) {
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
