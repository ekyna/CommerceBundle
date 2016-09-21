<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Product;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class AttributeGroupType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Product
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeGroupType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'ekyna_core.field.name',
            ]);
    }
}
