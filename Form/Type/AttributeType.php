<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\AdminBundle\Form\Type\ResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class AttributeType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeType extends ResourceFormType
{
    /**
     * @var
     */
    protected $attributeGroupClass;


    /**
     * Constructor.
     *
     * @param string $attributeClass
     * @param string $attributeGroupClass
     */
    public function __construct($attributeClass, $attributeGroupClass)
    {
        parent::__construct($attributeClass);

        $this->attributeGroupClass = $attributeGroupClass;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('group', ResourceType::class, [
                'label'     => 'ekyna_commerce.attribute_group.label.singular',
                'class'     => $this->attributeGroupClass,
                'allow_new' => true,
            ])
            ->add('name', TextType::class, [
                'label' => 'ekyna_core.field.name',
            ]);
    }
}
