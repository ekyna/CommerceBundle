<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShipmentItemType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentItemType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', Type\NumberType::class, [
                'label'          => 'ekyna_core.field.quantity',
                'attr'           => [
                    'class' => 'input-sm',
                ],
                'error_bubbling' => true,
                'disabled'       => $options['disabled'],
            ])
            ->add('children', ShipmentItemsType::class, [
                'entry_type'    => static::class,
                'entry_options' => [
                    'shipment' => $options['shipment'],
                    'level'    => $options['level'] + 1,
                    'disabled' => $options['disabled'],
                ],
            ]);
    }

    /**
     * @inheritdoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var ShipmentItemInterface $item */
        $item = $form->getData();
        $saleItem = $item->getSaleItem();

        $locked = false;
        if (null !== $parent = $saleItem->getParent()) {
            if ($parent->isPrivate() || ($parent->isCompound() && $parent->hasPrivateChildren())) {
                $locked = true;
            }
        }

        $view->vars['item'] = $item;
        $view->vars['level'] = $options['level'];
        $view->vars['return_mode'] = $options['shipment']->isReturn();

        $view->children['quantity']->vars['attr']['data-max'] = $item->getAvailable();

        if ($locked && isset($view->parent->parent->children['quantity'])) {
            $view->children['quantity']->vars['attr']['disabled'] = true;
            $view->children['quantity']->vars['attr']['data-quantity'] = $saleItem->getQuantity();
            $view->children['quantity']->vars['attr']['data-parent'] = $view->parent->parent->children['quantity']->vars['id'];
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

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'level'      => 0,
                'data_class' => $this->dataClass,
                'shipment'   => null,
            ])
            ->setAllowedTypes('level', 'int')
            ->setAllowedTypes('shipment', ShipmentInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_shipment_item';
    }
}
