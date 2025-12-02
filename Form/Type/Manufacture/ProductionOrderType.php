<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Manufacture;

use Ekyna\Bundle\CommerceBundle\Form\Type\Stock\WarehouseChoiceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function is_null;
use function Symfony\Component\Translation\t;

/**
 * Class ProductionOrderType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Manufacture
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionOrderType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', NumberType::class, [
                'label' => t('field.quantity', [], 'EkynaUi'),
            ])
            ->add('startAt', DateTimeType::class, [
                'label'    => t('field.start_date', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('endAt', DateTimeType::class, [
                'label'    => t('field.end_date', [], 'EkynaUi'),
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            $order = $event->getData();

            if (!$order instanceof ProductionOrderInterface) {
                throw new UnexpectedTypeException($order, ProductionOrderInterface::class);
            }

            $event
                ->getForm()
                ->add('bom', BillOfMaterialsChoiceType::class, [
                    'disabled' => !is_null($order->getBom()),
                ])
                ->add('warehouse', WarehouseChoiceType::class, [
                    'disabled' => !is_null($order->getWarehouse()),
                ]);
        });
    }
}
