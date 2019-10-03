<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\MoneyType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Payment\PaymentMethodChoiceType;
use Ekyna\Bundle\CommerceBundle\Model\ShipmentStates as BShipStates;
use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Shipment\Builder\ShipmentBuilderInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
    private $shipmentBuilder;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var string
     */
    private $defaultCurrency;


    /**
     * Constructor.
     *
     * @param ShipmentBuilderInterface      $shipmentBuilder
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param string                        $dataClass
     * @param string                        $defaultCurrency
     */
    public function __construct(
        ShipmentBuilderInterface $shipmentBuilder,
        AuthorizationCheckerInterface $authorizationChecker,
        $dataClass,
        $defaultCurrency
    ) {
        parent::__construct($dataClass);

        $this->shipmentBuilder = $shipmentBuilder;
        $this->authorizationChecker = $authorizationChecker;
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            /*->add('number', Type\TextType::class, [
                'label'    => 'ekyna_core.field.number',
                'required' => false,
                'disabled' => true,
            ])*/
            ->add('shippedAt', Type\DateTimeType::class, [
                'label'    => 'ekyna_commerce.shipment.field.shipped_at',
                'required' => false,
            ])
            ->add('description', Type\TextareaType::class, [
                'label'    => 'ekyna_commerce.field.description',
                'required' => false,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
                $shipment = $event->getData();

                if (null === $sale = $shipment->getSale()) {
                    throw new RuntimeException("The shipment must be associated with a sale at this point.");
                }
                if (!$sale instanceof OrderInterface) {
                    throw new RuntimeException("Not yet supported.");
                }

                $privileged = $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN');
                $locked = !$privileged && ShipmentStates::isStockableState($shipment->getState());

                if (!$locked) {
                    $this->shipmentBuilder->build($shipment);
                }

                if (!$sale->isSample() && $shipment->isReturn()) {
                    $autoInvoiceLabel = 'ekyna_commerce.shipment.field.auto_credit';

                    if (null === $shipment->getInvoice()) {
                        $form->add('creditMethod', PaymentMethodChoiceType::class, [
                            'label'       => 'ekyna_commerce.invoice.field.payment_method',
                            'required'    => false,
                            'public'      => false,
                            'outstanding' => false,
                            'invoice'     => $shipment->getInvoice(),
                            // TODO preferred choices
                            'attr'        => [
                                'help_text' => 'ekyna_commerce.shipment.message.credit_method',
                            ],
                        ]);
                    }
                } else {
                    $autoInvoiceLabel = 'ekyna_commerce.shipment.field.auto_invoice';
                }

                $form
                    ->add('state', Type\ChoiceType::class, [
                        'label'    => 'ekyna_core.field.status',
                        'choices'  => BShipStates::getFormChoices($shipment->isReturn(), !($locked || $privileged)),
                        'disabled' => $locked,
                    ])
                    ->add('weight', Type\NumberType::class, [
                        'label'    => 'ekyna_core.field.weight',
                        'scale'    => 3,
                        'required' => false,
                        'disabled' => $locked,
                        'attr'     => [
                            'placeholder' => 'ekyna_core.field.weight',
                            'input_group' => ['append' => 'kg'],
                            'min'         => 0,
                        ],
                    ])
                    ->add('valorization', MoneyType::class, [
                        'label'    => 'ekyna_commerce.shipment.field.valorization',
                        'currency' => $this->defaultCurrency,
                        'required' => false,
                        'disabled' => $locked,
                        'attr'     => [
                            'placeholder' => 'ekyna_commerce.shipment.field.valorization',
                        ],
                    ])
                    ->add('method', ShipmentMethodPickType::class, [
                        'available' => !$options['admin_mode'],
                        'return'    => $shipment->isReturn(),
                        'disabled'  => $locked,
                    ])
                    ->add('trackingNumber', Type\TextType::class, [
                        'label'    => 'ekyna_commerce.shipment.field.tracking_number',
                        'required' => false,
                        'disabled' => !empty($shipment->getTrackingNumber()),
                    ])
                    ->add('items', ShipmentTreeType::class, [
                        'entry_type' => $options['item_type'],
                        'shipment'   => $shipment,
                        'disabled'   => $locked,
                    ])
                    ->add('parcels', CollectionType::class, [
                        'label'        => 'ekyna_commerce.shipment.field.parcels',
                        'entry_type'   => $options['parcel_type'],
                        'allow_add'    => true,
                        'allow_delete' => true,
                        'required'     => false,
                        'disabled'     => $locked,
                    ])
                    // TODO Test post_submit event
                    ->add('receiverAddress', ShipmentAddressType::class, [
                        'label'    => 'ekyna_commerce.shipment.field.receiver_address',
                        'required' => false,
                        'disabled' => $locked,
                        'attr'     => [
                            'class' => 'shipment-receiver-address',
                        ],
                    ])
                    ->add('senderAddress', ShipmentAddressType::class, [
                        'label'    => 'ekyna_commerce.shipment.field.sender_address',
                        'required' => false,
                        'disabled' => $locked,
                        'attr'     => [
                            'class' => 'shipment-sender-address',
                        ],
                    ])
                    ->add('relayPoint', RelayPointType::class, [
                        'search' => $sale->isSameAddress() ? $sale->getInvoiceAddress() : $sale->getDeliveryAddress(),
                    ])
                    ->add('gatewayData', GatewayDataType::class, [
                        'disabled' => $locked,
                    ]);

                if (!$sale->isSample()) {
                    $form->add('autoInvoice', Type\CheckboxType::class, [
                        'label'    => $autoInvoiceLabel,
                        'disabled' => $locked || null !== $shipment->getInvoice(),
                        'required' => false,
                        'attr'     => [
                            'align_with_widget' => true,
                        ],
                    ]);
                }
            });
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface $shipment */
        $shipment = $form->getData();

        // For items layout
        $view->vars['return_mode'] = $shipment->isReturn();
        $view->vars['privileged'] = ShipmentStates::isStockableState($shipment->getState())
            && $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN');

        FormUtil::addClass($view, 'shipment');

        // For relay point ui (JS)
        if ($shipment->isReturn()) {
            FormUtil::addClass($view, 'return');
        }
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(['item_type'])
            ->setRequired(['parcel_type'])
            ->setAllowedTypes('item_type', 'string')
            ->setAllowedTypes('parcel_type', 'string');
    }
}
