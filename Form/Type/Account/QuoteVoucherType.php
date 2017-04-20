<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteAttachmentType;
use Ekyna\Bundle\CommerceBundle\Model\QuoteVoucher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

use function Symfony\Component\Translation\t;

/**
 * Class QuoteVoucherType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteVoucherType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', Type\TextType::class, [
                'label'       => t('sale.field.voucher_number', [], 'EkynaCommerce'),
                'required'    => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('attachment', QuoteAttachmentType::class, [
                'label' => false,
                'required'    => true,
                'constraints' => [
                    new Assert\Valid(),
                ],
                'attr' => [
                    'widget_col' => 12,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuoteVoucher::class,
        ]);
    }
}
