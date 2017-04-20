<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Sale;

use Ekyna\Bundle\UiBundle\Form\Type\FormStaticControlType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class SaleCouponType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Sale
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleCouponType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($code = $options['code']) {
            $builder
                ->add('code', FormStaticControlType::class, [
                    'label' => t('sale.field.coupon_code', [], 'EkynaCommerce'),
                    'data'  => $code,
                    'attr'  => [
                        'class' => 'input-sm sale-coupon-code text-success',
                    ],
                ])
                ->add('submit', SubmitType::class, [
                    'label' => t('button.remove', [], 'EkynaUi'),
                    'attr'  => [
                        'class' => 'btn-sm',
                    ],
                ]);

            return;
        }

        $builder
            ->add('code', TextType::class, [
                'label' => t('sale.field.coupon_code', [], 'EkynaCommerce'),
                'attr'  => [
                    'class' => 'input-sm sale-coupon-code',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => t('button.apply', [], 'EkynaUi'),
                'attr'  => [
                    'class' => 'btn-sm',
                ],
            ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr']['class'] = 'form-inline text-center';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('code', null)
            ->setAllowedTypes('code', ['null', 'string']);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_sale_coupon';
    }
}
