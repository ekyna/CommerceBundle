<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Checkout;

use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\RelayPointType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentMethodPickType;
use Ekyna\Bundle\CoreBundle\Form\Type\PhoneNumberType;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use libphonenumber\PhoneNumberType as PhoneType;
use libphonenumber\PhoneNumberUtil;
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
            /** @var CartInterface $sale */
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
                ->add('shipmentMethod', ShipmentMethodPickType::class, [
                    'label'    => false,
                    'sale'     => $sale,
                    'expanded' => true,
                    'attr'     => [
                        'class' => 'sale-shipment-method',
                    ],
                ])
                ->add('relayPoint', RelayPointType::class, [
                    'label'  => false,
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
                    'help_text' => 'ekyna_commerce.checkout.shipment.mobile_required',
                ],
                'number_attr'     => [
                    'class' => 'address-mobile',
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
