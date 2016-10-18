<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CoreBundle\Form\Type\HiddenEntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class ShipmentPriceType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPriceType extends ResourceFormType
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
     * @param string $dataClass
     * @param string $zoneClass
     * @param string $methodClass
     */
    public function __construct($dataClass, $zoneClass, $methodClass)
    {
        parent::__construct($dataClass);

        $this->zoneClass = $zoneClass;
        $this->methodClass = $methodClass;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('weight', NumberType::class, [
                'label'  => 'ekyna_core.field.weight',
                'sizing' => 'sm',
                'attr'   => [
                    'placeholder' => 'ekyna_core.field.weight',
                    'input_group' => ['append' => 'kg'],
                    'min'         => 0,
                ],
            ])
            ->add('netPrice', NumberType::class, [
                'label'  => 'ekyna_core.field.price',
                'scale'  => 5,
                'sizing' => 'sm',
                'attr'   => [
                    'placeholder' => 'ekyna_core.field.price',
                    'input_group' => ['append' => '€'],  // TODO by currency
                ],
            ])
            /*->add('zone', HiddenEntityType::class, [
                'class' => $this->zoneClass,
                'attr'  => [
                    'class' => 'shipment-price-zone',
                ],
            ])*/
            ->add('method', HiddenEntityType::class, [
                'class' => $this->methodClass,
                'attr'  => [
                    'class' => 'shipment-price-method',
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /** @var \Ekyna\Component\Commerce\Shipment\Entity\ShipmentPrice $price */
        if (($price = $form->getData()) && ($method = $price->getMethod())) {
            $view->vars['attr'] = array_merge($view->vars['attr'], [
                'data-method' => $method->getId()
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_shipment_price';
    }
}
