<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes as AM;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class CouponType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CouponType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('designation', Type\TextType::class, [
                'label'    => 'ekyna_core.field.designation',
                'required' => false,
            ])
            ->add('mode', Type\ChoiceType::class, [
                'label'             => 'ekyna_core.field.mode',
                'choices'           => AdjustmentModes::getChoices(),
                'preferred_choices' => [AM::MODE_FLAT],
                'select2'           => false,
                'attr'              => [
                    'placeholder' => 'ekyna_core.field.mode',
                ],
            ])
            ->add('amount', Type\NumberType::class, [
                'label' => 'ekyna_core.field.value',
                'scale' => 2,
                'attr'  => [
                    'placeholder' => 'ekyna_core.field.value',
                ],
            ])
            ->add('limit', Type\IntegerType::class, [
                'label' => 'ekyna_commerce.coupon.field.limit',
            ])
            ->add('startAt', Type\DateType::class, [
                'label'    => 'ekyna_core.field.from_date',
                'required' => false,
            ])
            ->add('endAt', Type\DateType::class, [
                'label'    => 'ekyna_core.field.to_date',
                'required' => false,
            ])
            ->add('minGross', Type\NumberType::class, [
                'label' => 'ekyna_commerce.coupon.field.min_gross',
                'scale' => 5,
            ])
            ->add('cumulative', Type\CheckboxType::class, [
                'label'    => 'ekyna_commerce.coupon.field.cumulative',
                'required' => false,
                'attr'     => [
                    'help_text' => 'ekyna_commerce.coupon.help.cumulative',
                    'align_with_widget' => true,
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var \Ekyna\Component\Commerce\common\Model\CouponInterface $coupon */
            $coupon = $event->getData();
            $form = $event->getForm();

            if ($coupon && $coupon->getId()) {
                $options = [
                    'required' => false,
                    'disabled' => true,
                ];
            } else {
                $options = [
                    'attr' => [
                        'help_text' => 'ekyna_commerce.coupon.alert.immutable_code',
                    ],
                ];
            }

            $form->add('code', Type\TextType::class, array_replace([
                'label' => 'ekyna_core.field.code',
            ], $options));
        });
    }
}
