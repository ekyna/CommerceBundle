<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Builder\ShipmentBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class ShipmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentType extends ResourceFormType
{
    /**
     * @var string
     */
    private $itemClass;

    /**
     * @var ShipmentBuilderInterface
     */
    private $shipmentBuilder;


    /**
     * Constructor.
     *
     * @param string $dataClass
     * @param string $itemClass
     */
    public function __construct($dataClass, $itemClass)
    {
        parent::__construct($dataClass);

        $this->itemClass = $itemClass;
    }

    /**
     * Sets the shipment builder.
     *
     * @param ShipmentBuilderInterface $shipmentBuilder
     */
    public function setShipmentBuilder(ShipmentBuilderInterface $shipmentBuilder)
    {
        $this->shipmentBuilder = $shipmentBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number', Type\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'disabled' => true,
            ])
            ->add('state', Type\ChoiceType::class, [
                'label'    => 'ekyna_core.field.status',
                'choices'  => ShipmentStates::getFormChoices(),
            ])
            ->add('method', ShipmentMethodChoiceType::class)
            ->add('trackingNumber', Type\TextType::class, [
                'label'    => 'ekyna_commerce.shipment.field.tracking_number',
                'required' => false,
            ])
            ->add('description', Type\TextareaType::class, [
                'label'    => 'ekyna_core.field.description',
                'required' => false,
            ])
            ->add('items', ShipmentItemsType::class, [
                'label'         => 'Items', // TODO
                'entry_options' => [
                    'data_class' => $this->itemClass,
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $data */
            $data = $event->getData();

            if (null === $data->getId()) {
                $this->shipmentBuilder->build($data);
            }

            $event->setData($data); // TODO ?
        });
    }
}
