<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\AttachmentType;
use Ekyna\Bundle\CommerceBundle\Model\SupplierOrderAttachmentTypes;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class SupplierOrderAttachmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderAttachmentType extends AttachmentType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', Type\TextType::class, [
                'label'    => t('field.title', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('type', ConstantChoiceType::class, [
                'label'    => t('field.type', [], 'EkynaUi'),
                'class'    => SupplierOrderAttachmentTypes::class,
                'required' => false,
            ]);
    }
}
