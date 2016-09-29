<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Builder\ShipmentBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
    private $methodClass;

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
     * @param string $methodClass
     * @param string $itemClass
     */
    public function __construct($dataClass, $methodClass, $itemClass)
    {
        parent::__construct($dataClass);

        $this->methodClass = $methodClass;
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
                'choices'  => ShipmentStates::getChoices(),
                'disabled' => true,
            ])
            ->add('method', EntityType::class, [
                'label'         => 'ekyna_commerce.shipment_method.label.singular',
                'class'         => $this->methodClass,
                'query_builder' => function (EntityRepository $repository) {
                    $qb = $repository
                        ->createQueryBuilder('m')
                        ->andWhere('m.enabled = :enabled')
                        ->setParameter('enabled', true);

                    return $qb;
                },
            ])
            ->add('description', Type\TextareaType::class, [
                'label' => 'ekyna_core.field.description',
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
