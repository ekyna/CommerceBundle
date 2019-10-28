<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CoreBundle\Form\Type\UploadType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
        if ($options['admin_mode']) {
            $builder->add('internal', CheckboxType::class, [
                'label'    => 'ekyna_commerce.field.internal',
                'required' => false,
                'attr'     => ['align_with_widget' => true],
            ]);
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Ekyna\Component\Commerce\Common\Model\SaleAttachmentInterface $data */
            $data = $event->getData();
            $form = $event->getForm();

            $lock = null !== $data->getType();

            $form->add('title', TextType::class, [
                'label'    => 'ekyna_core.field.title',
                'disabled' => $lock,
                'required' => false,
            ]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return UploadType::class;
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_attachment';
    }
}
