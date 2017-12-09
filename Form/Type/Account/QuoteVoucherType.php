<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Account;

use Ekyna\Bundle\CommerceBundle\Form\Type\Quote\QuoteAttachmentType;
use Ekyna\Bundle\CommerceBundle\Model\QuoteVoucher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class QuoteVoucherType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Account
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteVoucherType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number', Type\TextType::class, [
                'label'    => 'ekyna_commerce.sale.field.voucher_number',
                'required' => true,
            ])
            ->add('attachment', QuoteAttachmentType::class, [
                'required' => true,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => QuoteVoucher::class,
        ]);
    }
}
