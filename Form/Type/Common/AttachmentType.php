<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CoreBundle\Form\Type\UploadType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class AttachmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttachmentType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label'    => 'ekyna_core.field.title',
                'required' => false,
            ])
            ->add('internal', CheckboxType::class, [
                'label'    => 'ekyna_commerce.attachment.field.internal',
                'required' => false,
                'attr'     => ['align_with_widget' => true],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return UploadType::class;
    }
}
