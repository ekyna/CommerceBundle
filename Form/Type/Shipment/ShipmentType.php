<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\CommerceBundle\Model\ShipmentStates as BShipStates;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Shipment\Builder\ShipmentBuilderInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ShipmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentType extends AbstractResourceType
{
    private ShipmentBuilderInterface      $shipmentBuilder;
    private AuthorizationCheckerInterface $authorizationChecker;
    private string                        $defaultCurrency;

    public function __construct(
        ShipmentBuilderInterface $shipmentBuilder,
        AuthorizationCheckerInterface $authorizationChecker,
        string $defaultCurrency
    ) {
        $this->shipmentBuilder = $shipmentBuilder;
        $this->authorizationChecker = $authorizationChecker;
        $this->defaultCurrency = $defaultCurrency;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('shippedAt', Type\DateTimeType::class, [
                'label'    => t('shipment.field.shipped_at', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('description', Type\TextareaType::class, [
                'label'    => t('field.description', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                /** @var ShipmentInterface $shipment */
                $shipment = $event->getData();

                if (null === $sale = $shipment->getSale()) {
                    throw new RuntimeException('The shipment must be associated with a sale at this point.');
                }
                if (!$sale instanceof OrderInterface) {
                    throw new RuntimeException('Not yet supported.');
                }

                $privileged = $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN');
                $locked = !$privileged && ShipmentStates::isStockableState($shipment, false);

                if (!$locked) {
                    $this->shipmentBuilder->build($shipment);
                }

                $stateChoices = BShipStates::getFormChoices($shipment->isReturn(), !($locked || $privileged));

                $form
                    ->add('state', Type\ChoiceType::class, [
                        'label'                     => t('field.status', [], 'EkynaUi'),
                        'choices'                   => $stateChoices,
                        'choice_translation_domain' => BShipStates::getTranslationDomain(),
                        'disabled'                  => $locked,
                    ])
                    ->add('weight', Type\NumberType::class, [
                        'label'    => t('field.weight', [], 'EkynaUi'),
                        'decimal'    => true,
                        'scale'      => 3,
                        'required' => false,
                        'disabled' => $locked,
                        'attr'     => [
                            'placeholder' => t('field.weight', [], 'EkynaUi'),
                            'input_group' => ['append' => 'kg'],
                            'min'         => 0,
                        ],
                    ])
                    ->add('valorization', Type\MoneyType::class, [
                        'label'    => t('shipment.field.valorization', [], 'EkynaCommerce'),
                        'decimal'  => true,
                        'currency' => $this->defaultCurrency,
                        'required' => false,
                        'disabled' => $locked,
                        'attr'     => [
                            'placeholder' => t('shipment.field.valorization', [], 'EkynaCommerce'),
                        ],
                    ])
                    ->add('method', ShipmentMethodPickType::class, [
                        'available' => !$options['admin_mode'],
                        'return'    => $shipment->isReturn(),
                        'disabled'  => $locked,
                    ])
                    ->add('trackingNumber', Type\TextType::class, [
                        'label'    => t('shipment.field.tracking_number', [], 'EkynaCommerce'),
                        'required' => false,
                        'disabled' => !empty($shipment->getTrackingNumber()),
                    ])
                    ->add('items', ShipmentTreeType::class, [
                        'entry_type' => $options['item_type'],
                        'shipment'   => $shipment,
                        'disabled'   => $locked,
                    ])
                    ->add('parcels', CollectionType::class, [
                        'label'        => t('shipment.field.parcels', [], 'EkynaCommerce'),
                        'entry_type'   => $options['parcel_type'],
                        'allow_add'    => true,
                        'allow_delete' => true,
                        'required'     => false,
                        'disabled'     => $locked,
                    ])
                    // TODO Test post_submit event
                    ->add('receiverAddress', ShipmentAddressType::class, [
                        'label'    => t('shipment.field.receiver_address', [], 'EkynaCommerce'),
                        'required' => false,
                        'disabled' => $locked,
                        'attr'     => [
                            'class' => 'shipment-receiver-address',
                        ],
                    ])
                    ->add('senderAddress', ShipmentAddressType::class, [
                        'label'    => t('shipment.field.sender_address', [], 'EkynaCommerce'),
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
                        'label'    => t(
                            'shipment.field.' . ($shipment->isReturn() ? 'auto_credit' : 'auto_invoice'),
                            [], 'EkynaCommerce'
                        ),
                        'disabled' => $locked || null !== $shipment->getInvoice(),
                        'required' => false,
                        'attr'     => [
                            'align_with_widget' => true,
                        ],
                    ]);
                }
            });
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var ShipmentInterface $shipment */
        $shipment = $form->getData();

        // For items layout
        $view->vars['return_mode'] = $shipment->isReturn();
        $view->vars['privileged'] = ShipmentStates::isStockableState($shipment, false)
            && $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN');

        FormUtil::addClass($view, 'shipment');

        // For relay point ui (JS)
        if ($shipment->isReturn()) {
            FormUtil::addClass($view, 'return');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(['item_type'])
            ->setRequired(['parcel_type'])
            ->setAllowedTypes('item_type', 'string')
            ->setAllowedTypes('parcel_type', 'string');
    }
}
