<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\EventListener;

use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentPricingType;
use Ekyna\Bundle\UiBundle\Form\Type\ConfigurationType;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function array_replace_recursive;

/**
 * Class ShipmentMethodTypeSubscriber
 * @package Ekyna\Bundle\CommerceBundle\Form\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodTypeSubscriber implements EventSubscriberInterface
{
    private GatewayRegistryInterface $registry;


    public function __construct(GatewayRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function onPreSetData(FormEvent $event): void
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
            $platform->getConfigDefaults(), $method->getGatewayConfig()
        ));

        $form->add('config', ConfigurationType::class, [
            'definition'    => $definition,
            'property_path' => 'gatewayConfig',
        ]);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => ['onPreSetData'],
        ];
    }
}
