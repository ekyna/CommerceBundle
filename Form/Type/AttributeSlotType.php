<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AttributeSlotType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeSlotType extends ResourceFormType
{
    /**
     * @var
     */
    protected $attributeGroupClass;


    /**
     * Constructor.
     *
     * @param string $attributeSlotClass
     * @param string $attributeGroupClass
     */
    public function __construct($attributeSlotClass, $attributeGroupClass)
    {
        parent::__construct($attributeSlotClass);

        $this->attributeGroupClass = $attributeGroupClass;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('group', ResourceType::class, [
                'label'     => false,
                'class'     => $this->attributeGroupClass,
                'allow_new' => true,
                'attr'      => [
                    'widget_col' => 12,
                ],
            ])
            ->add('multiple', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.attribute_set.field.multiple',
                'required' => false,
            ])
            ->add('position', Type\HiddenType::class, [
                'attr' => [
                    'data-collection-role' => 'position',
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        /*$resolver->setDefaults([
            'attr' => ['widget_col']
        ])*/
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_attribute_slot';
    }


}
