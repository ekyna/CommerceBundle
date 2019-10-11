<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Quote;

use Ekyna\Bundle\AdminBundle\Form\Type\UserChoiceType;
use Ekyna\Bundle\CmsBundle\Form\Type\TagChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class QuoteType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Quote
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteType extends SaleType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('inCharge', UserChoiceType::class, [
                'label'    => 'ekyna_commerce.customer.field.in_charge',
                'required' => false,
            ])
            ->add('editable', CheckboxType::class, [
                'label'    => 'ekyna_commerce.quote.field.editable',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('expiresAt', DateTimeType::class, [
                'label'  => 'ekyna_core.field.expires_at',
                'format' => 'dd/MM/yyyy',
            ])
            ->add('tags', TagChoiceType::class, [
                'required' => false,
                'multiple' => true,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('address_type', QuoteAddressType::class);
    }
}
