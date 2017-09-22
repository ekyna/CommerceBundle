<?php

namespace Ekyna\Bundle\CommerceBundle\Form\EventListener;

use Ekyna\Bundle\CoreBundle\Form\Type\ConfigurationType;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class ShipmentMethodTypeSubscriber
 * @package Ekyna\Bundle\CommerceBundle\Form\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodTypeSubscriber implements EventSubscriberInterface
{
    /**
     * @var GatewayRegistryInterface
     */
    private $registry;


    /**
     * Constructor.
     *
     * @param GatewayRegistryInterface $registry
     */
    public function __construct(GatewayRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Pre set data event handler.
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        /** @var ShipmentMethodInterface $method */
        $method = $event->getData();

        $factory = $this->registry->getFactory($method->getFactoryName());

        $definition = $factory->getConfigDefinition();
        if ($definition instanceof ArrayNode && empty($definition->getChildren())) {
            return;
        }

        $event->getForm()->add('config', ConfigurationType::class, [
            'definition'    => $definition,
            'property_path' => 'gatewayConfig',
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => ['onPreSetData'],
        ];
    }
}
