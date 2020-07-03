<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Setting;

use Ekyna\Bundle\CommerceBundle\Service\Shipment\LabelRenderer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ShipmentLabelType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Setting
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentLabelType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('size', ChoiceType::class, [
                'label'    => 'ekyna_commerce.setting.shipment_label.size',
                'choices'  => LabelRenderer::getSizes(),
                'required' => false,
                'select2'  => false,
            ])
            ->add('width', IntegerType::class, [
                'label'    => 'ekyna_commerce.setting.shipment_label.width',
                'required' => false,
                'attr'     => [
                    'input_group' => ['append' => 'mm'],
                ],
            ])
            ->add('height', IntegerType::class, [
                'label'    => 'ekyna_commerce.setting.shipment_label.height',
                'required' => false,
                'attr'     => [
                    'input_group' => ['append' => 'mm'],
                ],
            ])
            ->add('margin', IntegerType::class, [
                'label'    => 'ekyna_commerce.setting.shipment_label.margin',
                'required' => false,
                'attr'     => [
                    'input_group' => ['append' => 'mm'],
                ],
            ])
            ->add('download', CheckboxType::class, [
                'label'    => 'ekyna_commerce.setting.shipment_label.download',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ]);
    }
}
