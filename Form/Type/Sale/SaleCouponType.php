<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormStaticControlType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SaleCouponType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleCouponType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($code = $options['code']) {
            $builder
                ->add('code', FormStaticControlType::class, [
                    'label' => 'ekyna_commerce.sale.field.coupon_code',
                    'data'  => $code,
                    'attr'  => [
                        'class' => 'input-sm sale-coupon-code text-success',
                    ],
                ])
                ->add('submit', SubmitType::class, [
                    'label' => 'ekyna_core.button.remove',
                    'attr'  => [
                        'class' => 'btn-sm',
                    ],
                ]);

            return;
        }

        $builder
            ->add('code', TextType::class, [
                'label' => 'ekyna_commerce.sale.field.coupon_code',
                'attr'  => [
                    'class' => 'input-sm sale-coupon-code',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'ekyna_core.button.apply',
                'attr'  => [
                    'class' => 'btn-sm',
                ],
            ]);
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['class'] = 'form-inline text-center';
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('code', null)
            ->setAllowedTypes('code', ['null', 'string']);
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_sale_coupon';
    }
}
