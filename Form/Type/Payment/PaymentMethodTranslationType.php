<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Payment;

use Ekyna\Bundle\CommerceBundle\Form\Type\Common\MethodTranslationType;
use Ekyna\Bundle\UiBundle\Form\Type\TinymceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class PaymentMethodTranslationType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Payment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentMethodTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('notice', TinymceType::class, [
                'label'    => t('payment_method.field.notice', [], 'EkynaCommerce'),
                'theme'    => 'light',
                'required' => false,
                //'admin_helper' => 'CMS_PAGE_CONTENT',
            ])
            ->add('footer', TinymceType::class, [
                'label'    => t('payment_method.field.footer', [], 'EkynaCommerce'),
                'theme'    => 'light',
                'required' => false,
                //'admin_helper' => 'CMS_PAGE_CONTENT',
            ]);
    }

    public function getParent(): ?string
    {
        return MethodTranslationType::class;
    }
}
