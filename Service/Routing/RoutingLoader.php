<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Service\Routing;

use Ekyna\Bundle\ResourceBundle\Service\Routing\Traits\PrefixTrait;
use Ekyna\Component\Commerce\Features;
use RuntimeException;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RoutingLoader
 * @package Ekyna\Bundle\CommerceBundle\Service\Routing
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RoutingLoader extends Loader
{
    use PrefixTrait;

    private const DIRECTORY = '@EkynaCommerceBundle/Resources/config/routing/front';

    private Features $features;
    private bool     $loaded = false;


    public function __construct(Features $feature, string $env = null)
    {
        parent::__construct($env);

        $this->features = $feature;
    }

    /**
     * @inheritDoc
     */
    public function load($resource, string $type = null)
    {
        if (true === $this->loaded) {
            throw new RuntimeException('Do not add the "commerce account" routes loader twice.');
        }

        $this->loaded = true;

        $collection = new RouteCollection();
        $accountCollection = new RouteCollection();

        if ($this->features->isEnabled(Features::LOYALTY)) {
            $routes = $this->import(self::DIRECTORY . '/account/loyalty.yaml', 'yaml');
            $this->addPrefixes($routes, [
                'en' => '/loyalty',
                'fr' => '/fidelite',
                'es' => '/lealtad',
            ]);
            $accountCollection->addCollection($routes);
        }

        if ($this->features->isEnabled(Features::NEWSLETTER)) {
            $prefixes = [
                'en' => '/newsletter',
                'fr' => '/newsletter',
                'es' => '/newsletter',
            ];
            $routes = $this->import(self::DIRECTORY . '/newsletter.yaml', 'yaml');
            $this->addPrefixes($routes, $prefixes);
            $collection->addCollection($routes);

            $routes = $this->import(self::DIRECTORY . '/account/newsletter.yaml', 'yaml');
            $this->addPrefixes($routes, $prefixes);
            $accountCollection->addCollection($routes);
        }

        if ($this->features->isEnabled(Features::SUPPORT)) {
            $routes = $this->import(self::DIRECTORY . '/account/ticket.yaml', 'yaml');
            $this->addPrefixes($routes, [
                'en' => '/tickets',
                'fr' => '/tickets',
                'es' => '/tickets',
            ]);
            $accountCollection->addCollection($routes);
        }

        if (0 < $accountCollection->count()) {
            // Should be configurable (sync with CMS)
            $this->addPrefixes($accountCollection, [
                'en' => '/my-account',
                'fr' => '/mon-compte',
                'es' => '/mi-cuenta',
            ]);

            $collection->addCollection($accountCollection);
        }

        return $collection;
    }

    /**
     * @inheritDoc
     */
    public function supports($resource, string $type = null): bool
    {
        return 'commerce_routing' === $type;
    }
}
