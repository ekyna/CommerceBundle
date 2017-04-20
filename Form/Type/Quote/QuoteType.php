<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Quote;

use Ekyna\Bundle\AdminBundle\Form\Type\UserChoiceType;
use Ekyna\Bundle\CmsBundle\Form\Type\TagChoiceType;
use Ekyna\Bundle\CommerceBundle\Form\Type\Sale\SaleType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class QuoteType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Quote
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteType extends SaleType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('inCharge', UserChoiceType::class, [
                'label'    => t('customer.field.in_charge', [], 'EkynaCommerce'),
                'required' => false,
            ])
            ->add('editable', CheckboxType::class, [
                'label'    => t('quote.field.editable', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('expiresAt', DateTimeType::class, [
                'label'  => t('field.expires_at', [], 'EkynaUi'),
            ])
            ->add('tags', TagChoiceType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('address_type', QuoteAddressType::class);
    }
}
