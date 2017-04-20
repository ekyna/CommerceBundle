<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_combine;
use function array_map;
use function Symfony\Component\Translation\t;
use function ucfirst;

/**
 * Class ShipmentPlatformChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPlatformChoiceType extends AbstractType
{
    protected GatewayRegistryInterface $registry;


    public function __construct(GatewayRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $names = $this->registry->getPlatformNames();

        $choices = array_combine(array_map(function ($name) {
            return ucfirst($name);
        }, $names), $names);

        $resolver->setDefaults([
            'label'                     => t('field.factory_name', [], 'EkynaCommerce'),
            'choice_translation_domain' => false,
            'choices'                   => $choices,
        ]);
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
