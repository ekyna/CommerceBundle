<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Payment;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MethodTranslationType;
use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PaymentMethodTranslationType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodTranslationType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('notice', TinymceType::class, [
                'label'    => 'ekyna_commerce.payment_method.field.notice',
                'theme'    => 'light',
                'required' => false,
                //'admin_helper' => 'CMS_PAGE_CONTENT',
            ])
            ->add('footer', TinymceType::class, [
                'label'    => 'ekyna_commerce.payment_method.field.footer',
                'theme'    => 'light',
                'required' => false,
                //'admin_helper' => 'CMS_PAGE_CONTENT',
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return MethodTranslationType::class;
    }
}
