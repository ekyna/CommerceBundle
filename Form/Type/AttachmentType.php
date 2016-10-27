<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CoreBundle\Form\Type\UploadType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class AttachmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttachmentType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('internal', CheckboxType::class, [
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
