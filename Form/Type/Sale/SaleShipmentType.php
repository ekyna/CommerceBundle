<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
            ->add('shipmentAmount', NumberType::class, [
                'label'    => 'ekyna_commerce.sale.field.shipment_total',
                'required' => false,
                'attr'     => [
                    'class' => 'sale-shipment-amount',
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
                    ->add('shipmentMethod', Shipment\ShipmentMethodChoiceType::class, [
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
                    'label'          => 'ekyna_core.field.mobile',
                    'property_path'  => $addressPath . '.mobile',
                    'required'       => false,
                    'default_region' => $region,
                    'format'         => PhoneNumberFormat::NATIONAL,
                    'attr'           => [
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

        $country = $sale->getDeliveryCountry();
        $method = $sale->getShipmentMethod();

        $view->vars['total_weight'] = $sale->getWeightTotal();
        $view->vars['delivery_country'] = $country;

        $price = null;
        if ($country && $method) {
            $price = $this
                ->shipmentPriceResolver
                ->getPriceByCountryAndMethodAndWeight($country, $method, $sale->getWeightTotal());
        }
        $view->vars['resolved_price'] = $price;
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
