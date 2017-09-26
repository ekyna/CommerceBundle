<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Component\Commerce\Shipment\Gateway\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShipmentPlatformChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPlatformChoiceType extends AbstractType
{
    /**
     * @var RegistryInterface
     */
    protected $registry;


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
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $names = $this->registry->getPlatformNames();

        $choices = array_combine(array_map(function ($name) {
            return ucfirst($name);
        }, $names), $names);

        $resolver->setDefaults([
            'label'   => 'ekyna_commerce.shipment_method.field.platform_name',
            'choices' => $choices,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
