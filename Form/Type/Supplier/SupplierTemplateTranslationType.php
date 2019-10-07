<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class SupplierTemplateTranslationType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierTemplateTranslationType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class, [
                'label'    => 'ekyna_core.field.subject',
                'required' => true,
            ])
            ->add('message', TinymceType::class, [
                'label'    => 'ekyna_core.field.message',
                'theme'    => 'front',
                'required' => true,
                'attr' => [
                    'help_text' => 'ekyna_commerce.supplier_template.help.message',
                ]
            ]);
    }
}
