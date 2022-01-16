<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Decimal\Decimal;
use Ekyna\Bundle\CommerceBundle\Form\FormHelper;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Component\Commerce\Common\Model\Units;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_merge;
use function array_unique;

/**
 * Class ShipmentItemType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentItemType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            /** @var ShipmentItemInterface $item */
            $item = $event->getData();

            $unit = Units::PIECE;
            $disabled = $options['disabled']; // || $this->>isDisabled($item);
            if ($saleItem = $item->getSaleItem()) {
                $unit = $saleItem->getUnit();
                $disabled = $disabled || $saleItem->isPrivate();
            }

            FormHelper::addQuantityType($event->getForm(), $unit, [
                'disabled'       => $disabled,
                'error_bubbling' => true,
                'attr'           => [
                    'class' => 'input-sm',
                ],
            ]);

            $event
                ->getForm()
                ->add('children', ShipmentItemsType::class, [
                    'entry_type'    => static::class,
                    'entry_options' => [
                        'shipment' => $options['shipment'],
                        'level'    => $options['level'] + 1,
                        'disabled' => $disabled,
                    ],
                ]);
        });
    }

    /*private function isDisabled(ShipmentItemInterface $item): bool
    {
        $saleItem = $item->getSaleItem();

        if (null === $parent = $saleItem->getParent()) {
            return false;
        }

        return $parent->isPrivate() || ($parent->isCompound() && $parent->hasPrivateChildren());
    }*/

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var ShipmentItemInterface $item */
        $item = $form->getData();
        /** @var ShipmentInterface $shipment */
        $shipment = $options['shipment'];
        $saleItem = $item->getSaleItem();
        $unit = $saleItem->getUnit();

        $view->vars['item'] = $item;
        $view->vars['level'] = $options['level'];
        $view->vars['return_mode'] = $shipment->isReturn();

        $view->children['quantity']->vars['attr']['data-max'] =
            Units::fixed($item->getAvailable() ?: new Decimal(0), $unit);

        if ($form->get('quantity')->isDisabled() && isset($view->parent->parent->children['quantity'])) {
            $view->children['quantity']->vars['attr']['data-quantity'] =
                Units::fixed($saleItem->getQuantity(), $unit);
            $view->children['quantity']->vars['attr']['data-parent'] =
                $view->parent->parent->children['quantity']->vars['id'];
        }

        // Geocode
        $geocodes = [];
        if ($saleItem instanceof StockAssignmentsInterface) {
            foreach ($saleItem->getStockAssignments() as $assignment) {
                $geocodes = array_merge($geocodes, $assignment->getStockUnit()->getGeocodes());
            }
        }
        $geocodes = array_unique($geocodes);

        $view->vars['geocodes'] = $geocodes;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'level'    => 0,
                'shipment' => null,
            ])
            ->setAllowedTypes('level', 'int')
            ->setAllowedTypes('shipment', ShipmentInterface::class);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_shipment_item';
    }
}
