<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShipmentItemsType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentItemsType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var \Doctrine\Common\Collections\Collection $items */
            $items = $event->getData();

            /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface $item */
            foreach ($items as $item) {
                if (0 == $item->getQuantity()) {
                    $items->removeElement($item);
                    $item->setShipment(null);
                }
            }

            //$event->getForm()->setData($items);
            $event->setData($items);
        }, 51); // Before collection type's submit event listener
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type'     => ShipmentItemType::class,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_shipment_items';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
