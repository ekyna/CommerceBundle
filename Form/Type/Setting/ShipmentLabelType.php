<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Setting;

use Ekyna\Bundle\CommerceBundle\Service\Shipment\ShipmentLabelRenderer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

/**
 * Class ShipmentLabelType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Setting
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentLabelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('size', ChoiceType::class, [
                'label'                     => t('setting.shipment_label.size', [], 'EkynaCommerce'),
                'choices'                   => ShipmentLabelRenderer::getSizes(),
                'choice_translation_domain' => false,
                'required'                  => false,
                'select2'                   => false,
            ])
            ->add('width', IntegerType::class, [
                'label'    => t('setting.shipment_label.width', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'input_group' => ['append' => 'mm'],
                ],
            ])
            ->add('height', IntegerType::class, [
                'label'    => t('setting.shipment_label.height', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'input_group' => ['append' => 'mm'],
                ],
            ])
            ->add('margin', IntegerType::class, [
                'label'    => t('setting.shipment_label.margin', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'input_group' => ['append' => 'mm'],
                ],
            ])
            ->add('download', CheckboxType::class, [
                'label'    => t('setting.shipment_label.download', [], 'EkynaCommerce'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);
    }
}
