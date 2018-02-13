<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AttachmentType;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderAttachmentTypes;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class SupplierOrderAttachmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderAttachmentType extends AttachmentType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', Type\TextType::class, [
                'label'    => 'ekyna_core.field.title',
                'required' => false,
            ])
            ->add('type', Type\ChoiceType::class, [
                'label'    => 'ekyna_core.field.type',
                'choices'  => SupplierOrderAttachmentTypes::getChoices(),
                'required' => false,
            ]);
    }
}
