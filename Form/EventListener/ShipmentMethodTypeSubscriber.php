<?php

namespace Ekyna\Bundle\CommerceBundle\Form\EventListener;

use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentPricingType;
use Ekyna\Bundle\CoreBundle\Form\Type\ConfigurationType;
use Ekyna\Component\Commerce\Shipment\Gateway\RegistryInterface;
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
     * @var RegistryInterface
     */
    private $registry;


    /**
     * Constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
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
        $form = $event->getForm();
        /** @var ShipmentMethodInterface $method */
        $method = $event->getData();

        if (0 < $method->getId()) {
            $form->add('pricing', ShipmentPricingType::class, [
                'filter_by' => 'zone',
            ]);
        }

        $platform = $this->registry->getPlatform($method->getPlatformName());

        $definition = $platform->getConfigDefinition();
        if ($definition instanceof ArrayNode && empty($definition->getChildren())) {
            return;
        }

        $method->setGatewayConfig(array_replace_recursive(
            $platform->getConfigDefaults(), (array)$method->getGatewayConfig()
        ));

        $form->add('config', ConfigurationType::class, [
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
