<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\DataTransformer\ShipmentItemsDataTransformer;
use Ekyna\Bundle\CommerceBundle\Model\ShipmentStates as BShipStates;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Shipment\Builder\ShipmentBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShipmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentType extends ResourceFormType
{
    /**
     * @var ShipmentBuilderInterface
     */
    private $builder;


    /**
     * Constructor.
     *
     * @param ShipmentBuilderInterface $builder
     * @param string                   $dataClass
     */
    public function __construct(ShipmentBuilderInterface $builder, $dataClass)
    {
        parent::__construct($dataClass);

        $this->builder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number', Type\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'required' => false,
                'disabled' => true,
            ])
            ->add('method', ShipmentMethodChoiceType::class, [
                'available' => !$options['admin_mode'],
            ])
            ->add('weight', Type\NumberType::class, [
                'label'    => 'ekyna_core.field.weight',
                'scale'    => 3,
                'required' => false,
                'attr'     => [
                    'placeholder' => 'ekyna_core.field.weight',
                    'input_group' => ['append' => 'kg'],
                    'min'         => 0,
                ],
            ])
            ->add('trackingNumber', Type\TextType::class, [
                'label'    => 'ekyna_commerce.shipment.field.tracking_number',
                'required' => false,
            ])
            ->add('description', Type\TextareaType::class, [
                'label'    => 'ekyna_core.field.description',
                'required' => false,
            ])
            ->add('items', ShipmentItemsType::class, [
                'entry_type' => $options['item_type'],
            ])
            ->add('receiverAddress', ShipmentAddressType::class, [
                'label'    => 'ekyna_commerce.shipment.field.receiver_address',
                'required' => false,
            ])
            ->add('senderAddress', ShipmentAddressType::class, [
                'label'    => 'ekyna_commerce.shipment.field.sender_address',
                'required' => false,
            ])
            ->addModelTransformer(new ShipmentItemsDataTransformer($this->builder))
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $form = $event->getForm();
                /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
                $shipment = $event->getData();

                if (null === $sale = $shipment->getSale()) {
                    throw new RuntimeException("The shipment must be associated with a sale at this point.");
                }
                if (!$sale instanceof OrderInterface) {
                    throw new RuntimeException("Not yet supported.");
                }

                $availableStateChoices = BShipStates::getFormChoices(
                    $shipment->isReturn(),
                    !OrderStates::isStockableState($sale->getState())
                );

                if (null === $shipment->getId() || null !== $shipment->getInvoice()) {
                    $shipment->setAutoInvoice(true);
                }

                $form
                    ->add('state', Type\ChoiceType::class, [
                        'label'   => 'ekyna_core.field.status',
                        'choices' => $availableStateChoices,
                    ])
                    ->add('autoInvoice', Type\CheckboxType::class, [
                        'label'    => 'ekyna_commerce.shipment.field.auto_invoice',
                        'required' => false,
                        'attr'     => [
                            'align_with_widget' => true,
                        ],
                    ]);
            });
    }

    /**
     * @inheritdoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
        $shipment = $form->getData();

        $view->vars['return_mode'] = $shipment->isReturn();
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'shipment');
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(['item_type'])
            ->setAllowedTypes('item_type', 'string');
    }
}
