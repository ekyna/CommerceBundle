<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Stock;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CommerceBundle\Model\StockAdjustmentReasons;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class StockAdjustmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAdjustmentType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', Type\NumberType::class, [
                'label' => 'ekyna_core.field.quantity',
            ])
            ->add('reason', Type\ChoiceType::class, [
                'label'       => 'ekyna_commerce.stock_adjustment.field.reason',
                'placeholder' => 'ekyna_core.value.choose',
                'choices'     => StockAdjustmentReasons::getChoices(),
            ])
            ->add('note', Type\TextType::class, [
                'label'    => 'ekyna_core.field.comment',
                'required' => false,
            ]);
    }
}