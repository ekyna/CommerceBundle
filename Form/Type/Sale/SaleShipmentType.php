<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;
use Ekyna\Bundle\UiBundle\Form\Type\PhoneNumberType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
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

use function Symfony\Component\Translation\t;

/**
 * Class SaleShipmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleShipmentType extends AbstractType
{
    private ShipmentPriceResolverInterface $shipmentPriceResolver;


    public function __construct(ShipmentPriceResolverInterface $shipmentPriceResolver)
    {
        $this->shipmentPriceResolver = $shipmentPriceResolver;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('shipmentAmount', Type\NumberType::class, [
                'label'    => t('sale.field.shipping_cost', [], 'EkynaCommerce'),
                'decimal'  => true,
                'scale'    => 5,
                'required' => true,
                'attr'     => [
                    'class' => 'sale-shipment-amount',
                ],
            ])
            ->add('shipmentWeight', Type\NumberType::class, [
                'label'    => t('sale.field.weight_total', [], 'EkynaCommerce'),
                'decimal'  => true,
                'scale'    => 3,
                'required' => false,
                'attr'     => [
                    'class' => 'sale-shipment-weight',
                ],
            ])
            ->add('shipmentLabel', Type\TextType::class, [
                'label'    => t('sale.field.shipment_label', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'class' => 'sale-shipment-label',
                ],
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
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
                        'label'      => t('shipment_method.label.singular', [], 'EkynaCommerce'),
                        'subject'    => $sale,
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
                if (null !== $country = $address->getCountry()) {
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

    public function buildView(FormView $view, FormInterface $form, array $options): void
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

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        FormUtil::addClass($view, 'commerce-sale-shipment');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SaleInterface::class,
        ]);
    }
}
