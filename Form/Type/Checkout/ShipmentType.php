<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Checkout;

use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentMethodChoiceType;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShipmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Checkout
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $sale = $event->getData();
            $form = $event->getForm();

            $form->add('shipmentMethod', ShipmentMethodChoiceType::class, [
                'sale'     => $sale,
                'expanded' => true,
                'attr'     => [
                    'class' => 'sale-shipment-method',
                ],
            ]);
        });
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CartInterface::class);
    }
}
