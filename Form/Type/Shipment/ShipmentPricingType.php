<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class ShipmentPricingType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPricingType extends Form\AbstractType
{
    private string $zoneClass;
    private string $methodClass;


    public function __construct(string $zoneClass, string $methodClass)
    {
        $this->zoneClass = $zoneClass;
        $this->methodClass = $methodClass;
    }

    public function buildForm(Form\FormBuilderInterface $builder, array $options): void
    {
        $class = $options['filter_by'] === 'zone' ? $this->zoneClass : $this->methodClass;

        $builder
            ->add('filter', EntityType::class, [
                'label'   => false,
                'class'   => $class,
                'mapped'  => false,
                'select2' => false,
                'attr'    => [
                    'data-filter-by' => $options['filter_by'],
                    'class'          => 'commerce-shipment-pricing-filter',
                ],
            ])
            ->add('prices', ShipmentPricesType::class, [
                //'label'     => t('shipment_price.label.plural', [], 'EkynaCommerce'),
                'label'     => false,
                'required'  => false,
                'filter_by' => $options['filter_by'],
                'attr'      => [
                    'class' => 'commerce-shipment-pricing-prices',
                ],
            ]);
    }

    public function finishView(Form\FormView $view, Form\FormInterface $form, array $options): void
    {
        FormUtil::addClass($view, 'commerce-shipment-pricing');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'label'        => t('shipment_price.label.plural', [], 'EkynaCommerce'),
                'inherit_data' => true,
                'attr'         => [
                    'class' => 'commerce-shipment-pricing',
                ],
            ])
            ->setRequired('filter_by')
            ->setAllowedValues('filter_by', ['zone', 'method']);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_shipment_pricing';
    }
}
