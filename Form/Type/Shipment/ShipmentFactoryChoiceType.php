<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShipmentMethodFactoryChoiceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentFactoryChoiceType extends AbstractType
{
    /**
     * @var GatewayRegistryInterface
     */
    protected $registry;


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
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $names = $this->registry->getFactoryNames();

        $choices = array_combine(array_map(function ($name) {
            return ucfirst($name);
        }, $names), $names);

        $resolver->setDefaults([
            'label'   => 'ekyna_commerce.shipment_method.field.factory_name',
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
