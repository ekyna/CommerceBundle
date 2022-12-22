<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Stock;

use Ekyna\Bundle\CommerceBundle\Form\FormHelper;
use Ekyna\Bundle\CommerceBundle\Model\StockAdjustmentReasons;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ConstantChoiceType;
use Ekyna\Component\Commerce\Common\Model\Units;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class StockAdjustmentType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAdjustmentType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var StockAdjustmentInterface $adjustment */
            $adjustment = $event->getData();

            $unit = $this->getUnit($adjustment);

            FormHelper::addQuantityType($event->getForm(), $unit);
        });

        $builder
            ->add('reason', ConstantChoiceType::class, [
                'label'       => t('stock_adjustment.field.reason', [], 'EkynaCommerce'),
                'placeholder' => t('value.choose', [], 'EkynaUi'),
                'class'       => StockAdjustmentReasons::class,
            ])
            ->add('note', TextType::class, [
                'label'    => t('field.comment', [], 'EkynaUi'),
                'required' => false,
            ]);
    }

    private function getUnit(StockAdjustmentInterface $adjustment): string
    {
        if (null === $stockUnit = $adjustment->getStockUnit()) {
            return Units::PIECE;
        }

        if (null === $subject = $stockUnit->getSubject()) {
            return Units::PIECE;
        }

        return $subject->getUnit() ?: Units::PIECE;
    }
}
