<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;
use Ekyna\Bundle\CoreBundle\Form\Type\PhoneNumberType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;
use libphonenumber\PhoneNumberType as PhoneType;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SaleShipmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleShipmentType extends AbstractType
{
    /**
     * @var ShipmentPriceResolverInterface
     */
    private $shipmentPriceResolver;


    /**
     * Constructor.
     *
     * @param ShipmentPriceResolverInterface $shipmentPriceResolver
     */
    public function __construct(ShipmentPriceResolverInterface $shipmentPriceResolver)
    {
        $this->shipmentPriceResolver = $shipmentPriceResolver;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shipmentAmount', Type\NumberType::class, [
                'label'    => 'ekyna_commerce.sale.field.shipping_cost',
                'scale'    => 5,
                'required' => true,
                'attr'     => [
                    'class' => 'sale-shipment-amount',
                ],
            ])
            ->add('shipmentWeight', Type\NumberType::class, [
                'label'    => 'ekyna_commerce.sale.field.weight_total',
                'scale'    => 3,
                'required' => false,
                'attr'     => [
                    'class' => 'sale-shipment-weight',
                ],
            ])
            ->add('shipmentLabel', Type\TextType::class, [
                'label'    => 'ekyna_commerce.sale.field.shipment_label',
                'required' => false,
                'attr'     => [
                    'class' => 'sale-shipment-label',
                ],
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                /** @var SaleInterface $sale */
                $sale = $event->getData();
                $form = $event->getForm();

                if ($sale->isSameAddress()) {
                    $addressPath = 'invoiceAddress';
                    $address = $sale->getInvoiceAddress();
                } else {
                    $addressPath = 'deliveryAddress';
                    $address = $sale->getDeliveryAddress();
                }

                $form
                    ->add('shipmentMethod', Shipment\ShipmentMethodPickType::class, [
                        'label'      => 'ekyna_commerce.shipment_method.label.singular',
                        'sale'       => $sale,
                        'with_price' => false,
                        'available'  => false,
                        'attr'       => [
                            'class' => 'sale-shipment-method',
                        ],
                    ])
                    ->add('relayPoint', Shipment\RelayPointType::class, [
                        'search' => $address,
                    ]);

                if (is_null($address) || !is_null($address->getMobile())) {
                    return;
                }

                $region = PhoneNumberUtil::UNKNOWN_REGION;
                if ($address && null !== $country = $address->getCountry()) {
                    $region = $country->getCode();
                }

                $form->add('mobile', PhoneNumberType::class, [
                    'label'           => 'ekyna_core.field.mobile',
                    'property_path'   => $addressPath . '.mobile',
                    'required'        => false,
                    'default_country' => $region,
                    'type'            => PhoneType::MOBILE,
                    'attr'            => [
                        'class'     => 'address-mobile',
                        'help_text' => 'ekyna_commerce.checkout.shipment.mobile_required',
                    ],
                ]);
            });
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /** @var SaleInterface $sale */
        $sale = $form->getData();

        $view->vars['total_weight'] = $sale->getWeightTotal();
        $view->vars['delivery_country'] = $sale->getDeliveryCountry();
        $view->vars['resolved_price'] = 0;

        if (null !== $price = $this->shipmentPriceResolver->getPriceBySale($sale)) {
            $view->vars['resolved_price'] = $price->isFree() ? 0 : $price->getPrice();
        }
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'commerce-sale-shipment');
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SaleInterface::class,
        ]);
    }
}
