<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler;

use Ekyna\Bundle\CommerceBundle\EventListener\AddressListener;
use Ekyna\Bundle\CommerceBundle\Service\Serializer\Type\FormErrorType;
use Ekyna\Component\Commerce\Cart\Event\CartAddressEvents;
use Ekyna\Component\Commerce\Customer\Event\CustomerAddressEvents;
use Ekyna\Component\Commerce\Order\Event\OrderAddressEvents;
use Ekyna\Component\Commerce\Quote\Event\QuoteAddressEvents;
use Ekyna\Component\Commerce\Shipment\Event\RelayPointEvents;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class GeocodingPass
 * @package Ekyna\Bundle\CommerceBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class GeocodingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('geocoder')) {
            return;
        }

        // Address event listener (geocoding)
        $definition = $container
            ->register('ekyna_commerce.listener.address', AddressListener::class)
            ->setArguments([
                new Reference('ekyna_resource.orm.persistence_helper'),
                new Reference('geocoder'),
            ]);

        $events = [
            CustomerAddressEvents::INSERT => 'onInsert',
            CustomerAddressEvents::UPDATE => 'onUpdate',
            CartAddressEvents::INSERT     => 'onInsert',
            CartAddressEvents::UPDATE     => 'onUpdate',
            QuoteAddressEvents::INSERT    => 'onInsert',
            QuoteAddressEvents::UPDATE    => 'onUpdate',
            OrderAddressEvents::INSERT    => 'onInsert',
            OrderAddressEvents::UPDATE    => 'onUpdate',
            RelayPointEvents::INSERT      => 'onInsert',
            RelayPointEvents::UPDATE      => 'onUpdate',
        ];

        foreach ($events as $name => $method) {
            $definition->addTag('resource.event_listener', [
                'event'    => $name,
                'method'   => $method,
                'priority' => -2048,
            ]);
        }
    }
}
