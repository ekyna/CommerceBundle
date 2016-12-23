<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShipmentPricingType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPricingType extends Form\AbstractType
{
    /**
     * @var string
     */
    private $zoneClass;

    /**
     * @var string
     */
    private $methodClass;


    /**
     * Constructor.
     *
     * @param string $zoneClass
     * @param string $methodClass
     */
    public function __construct($zoneClass, $methodClass)
    {
        $this->zoneClass = $zoneClass;
        $this->methodClass = $methodClass;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(Form\FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('filter', EntityType::class, [
                //'label'  => 'ekyna_commerce.shipment_' . $options['filter_by'] . '.label.singular',
                'label'  => false,
                'class'  => $this->{$options['filter_by'] . 'Class'},
                'mapped' => false,
                'attr'   => [
                    'data-filter-by' => $options['filter_by'],
                    'class'          => 'commerce-shipment-pricing-filter no-select2',
                ],
            ])
            ->add('prices', ShipmentPricesType::class, [
                //'label'     => 'ekyna_commerce.shipment_price.label.plural',
                'label'     => false,
                'filter_by' => $options['filter_by'],
                'attr'      => [
                    'class' => 'commerce-shipment-pricing-prices',
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function finishView(Form\FormView $view, Form\FormInterface $form, array $options)
    {
        FormUtil::addClass($view, 'commerce-shipment-pricing');
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'        => 'ekyna_commerce.shipment_price.label.plural',
                'inherit_data' => true,
                'attr'         => [
                    'class' => 'commerce-shipment-pricing',
                ],
            ])
            ->setRequired('filter_by')
            ->setAllowedValues('filter_by', ['zone', 'method']);
    }


    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_shipment_pricing';
    }
}
