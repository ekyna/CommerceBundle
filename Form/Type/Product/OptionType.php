<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Product;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class OptionType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Product
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionType extends ResourceFormType
{
    /**
     * @var string
     */
    private $taxGroupClass;


    /**
     * Constructor.
     *
     * @param string $optionClass
     * @param string $taxGroupClass
     */
    public function __construct($optionClass, $taxGroupClass)
    {
        parent::__construct($optionClass);

        $this->taxGroupClass = $taxGroupClass;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('designation', Type\TextType::class, [
                'label'    => 'ekyna_core.field.designation',
                'sizing' => 'sm',
                'required' => false,
                'attr'   => [
                    'placeholder' => 'ekyna_core.field.designation',
                ],
            ])
            ->add('reference', Type\TextType::class, [
                'label' => 'ekyna_core.field.reference',
                'sizing' => 'sm',
                'attr'   => [
                    'placeholder' => 'ekyna_core.field.reference',
                ],
            ])
            ->add('netPrice', Type\NumberType::class, [
                'label'  => 'ekyna_commerce.order_item.field.net_unit_price', // TODO
                'sizing' => 'sm',
                'scale'  => 5,
                'attr'   => [
                    'placeholder' => 'ekyna_commerce.order_item.field.net_unit_price', // TODO
                    'input_group' => ['append' => '€'],
                ],
            ])
            ->add('taxGroup', ResourceType::class, [
                'label'    => 'ekyna_commerce.tax_group.label.singular',
                'sizing' => 'sm',
                'class'    => $this->taxGroupClass,
                'attr'   => [
                    'placeholder' => 'ekyna_commerce.tax_group.label.singular',
                ],
            ])
            ->add('position', Type\HiddenType::class,[
                'attr' => [
                    'data-collection-role' => 'position',
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_option';
    }
}
