<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Common;

use Ekyna\Bundle\CommerceBundle\Model\AdjustmentModes;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes as AM;
use Ekyna\Component\Commerce\common\Model\CouponInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function array_replace;
use function Symfony\Component\Translation\t;

/**
 * Class CouponType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CouponType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('designation', Type\TextType::class, [
                'label'    => t('field.designation', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('mode', ConstantChoiceType::class, [
                'label'             => t('field.mode', [], 'EkynaUi'),
                'class'             => AdjustmentModes::class,
                'preferred_choices' => [AM::MODE_FLAT],
                'select2'           => false,
                'attr'              => [
                    'placeholder' => t('field.mode', [], 'EkynaUi'),
                ],
            ])
            ->add('amount', Type\NumberType::class, [
                'label'   => t('field.value', [], 'EkynaUi'),
                'decimal' => true,
                'scale'   => 2,
                'attr'    => [
                    'placeholder' => 'field.value',
                ],
            ])
            ->add('limit', Type\IntegerType::class, [
                'label' => t('coupon.field.limit', [], 'EkynaCommerce'),
            ])
            ->add('startAt', Type\DateType::class, [
                'label'    => t('field.from_date', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('endAt', Type\DateType::class, [
                'label'    => t('field.to_date', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('minGross', Type\NumberType::class, [
                'label'   => t('coupon.field.min_gross', [], 'EkynaCommerce'),
                'decimal' => true,
                'scale'   => 5,
            ])
            ->add('cumulative', Type\CheckboxType::class, [
                'label'    => t('coupon.field.cumulative', [], 'EkynaCommerce'),
                'required' => false,
                'help'     => t('coupon.help.cumulative', [], 'EkynaCommerce'),
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var CouponInterface $coupon */
            $coupon = $event->getData();
            $form = $event->getForm();

            if ($coupon && $coupon->getId()) {
                $options = [
                    'required' => false,
                    'disabled' => true,
                ];
            } else {
                $options = [
                    'help' => t('coupon.alert.immutable_code', [], 'EkynaCommerce'),
                ];
            }

            $form->add(
                'code',
                Type\TextType::class,
                array_replace([
                    'label' => t('field.code', [], 'EkynaUi'),
                ], $options)
            );
        });
    }
}
