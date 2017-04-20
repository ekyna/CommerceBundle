<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\UiBundle\Form\Type\UploadType;
use Ekyna\Component\Commerce\Common\Model\SaleAttachmentInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class AttachmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttachmentType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['admin_mode']) {
            $builder->add('internal', CheckboxType::class, [
                'label'    => t('field.internal', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => ['align_with_widget' => true],
            ]);
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var SaleAttachmentInterface $data */
            $data = $event->getData();
            $form = $event->getForm();

            $lock = null !== $data->getType();

            $form->add('title', TextType::class, [
                'label'    => t('field.title', [], 'EkynaUi'),
                'disabled' => $lock,
                'required' => false,
            ]);
        });
    }

    public function getParent(): ?string
    {
        return UploadType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_attachment';
    }
}
