<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\CommerceBundle\Form\DataTransformer\ShipmentItemsDataTransformer;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShipmentTreeType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentTreeType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addModelTransformer(new ShipmentItemsDataTransformer($options['shipment']))
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                $data = $form->getNormData();

                if (null === $data) {
                    $data = [];
                }

                if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
                    throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
                }

                // First remove all rows
                foreach ($form as $name => $child) {
                    $form->remove($name);
                }

                // Then add all rows again in the correct order
                foreach ($data as $name => $value) {
                    $form->add($name, $options['entry_type'], array_replace([
                        'property_path' => '[' . $name . ']',
                    ], $options['entry_options']));
                }
            });
    }


    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['headers'] = true;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'shipment'      => null,
                'label'         => 'ekyna_commerce.shipment.field.items',
                'entry_type'    => ShipmentItemType::class,
                'entry_options' => [],
            ])
            ->setAllowedTypes('shipment', ShipmentInterface::class)
            ->setNormalizer('entry_options', function (Options $options, $value) {
                $value['shipment'] = $options['shipment'];

                return $value;
            });
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_shipment_items';
    }
}