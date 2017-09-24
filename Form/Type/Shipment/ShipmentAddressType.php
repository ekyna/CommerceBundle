<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AddressType;
use Ekyna\Component\Commerce\Bridge\Symfony\Transformer\ShipmentAddressTransformer;
use Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints\Address;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShipmentAddressType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentAddressType extends AbstractType
{
    /**
     * @var ShipmentAddressTransformer
     */
    private $transformer;


    /**
     * Constructor.
     *
     * @param ShipmentAddressTransformer $transformer
     */
    public function __construct(ShipmentAddressTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->transformer);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'  => ShipmentAddress::class,
                'constraints' => [new Address()],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return AddressType::class;
    }
}
