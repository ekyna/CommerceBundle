<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Checkout;

use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\RelayPointType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentMethodPickType;
use Ekyna\Bundle\UiBundle\Form\Type\PhoneNumberType;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use libphonenumber\PhoneNumberType as PhoneType;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class ShipmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Checkout
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
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
                'label'           => t('field.mobile', [], 'EkynaUi'),
                'property_path'   => $addressPath . '.mobile',
                'required'        => false,
                'default_country' => $region,
                'type'            => PhoneType::MOBILE,
                'attr'            => [
                    'class'     => 'address-mobile',
                    'help_text' => t('checkout.shipment.mobile_required', [], 'EkynaCommerce'),
                ],
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', CartInterface::class);
    }
}
