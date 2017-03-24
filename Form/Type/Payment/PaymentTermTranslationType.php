<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Payment;

use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PaymentTermTranslationType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentTermTranslationType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'ekyna_core.field.title',
//                'admin_helper' => 'CMS_PAGE_TITLE',
            ])
            ->add('description', TinymceType::class, [
                'label'    => 'ekyna_core.field.description',
//                'admin_helper' => 'CMS_PAGE_CONTENT',
                'theme'    => 'front',
                'required' => false,
            ]);
    }
}
