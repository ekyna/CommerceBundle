<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Bundle\CommerceBundle\Form\Type\Shipment\ShipmentMethodChoiceType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormAttributes;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentPriceResolverInterface;
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
            ]);

        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $sale = $event->getData();
                $form = $event->getForm();

                $form->add('preferredShipmentMethod', ShipmentMethodChoiceType::class, [
                    'label' => 'ekyna_commerce.shipment_method.label.singular',
                    'sale'  => $sale,
                    'attr'  => [
                        'class' => 'sale-shipment-method',
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

        $deliveryAddress = $sale->isSameAddress()
            ? $sale->getInvoiceAddress()
            : $sale->getDeliveryAddress();

        $country = $deliveryAddress ? $deliveryAddress->getCountry() : null;
        $method = $sale->getPreferredShipmentMethod();

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
        FormAttributes::addClass($view, 'commerce-sale-shipment');
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
