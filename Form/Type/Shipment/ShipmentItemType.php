<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
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
                'disabled'       => 0 < $options['level'],
                'attr'           => [
                    'class' => 'input-sm',
                ],
                'error_bubbling' => true,
            ])
            ->add('children', ShipmentItemsType::class, [
                'headers'       => false,
                'entry_type'    => static::class,
                'entry_options' => [
                    'level' => $options['level'] + 1,
                ],
            ]);
    }

    /**
     * @inheritdoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /** @var ShipmentItemInterface $item */
        $item = $form->getData();

        $view->vars['item'] = $item;
        $view->vars['level'] = $options['level'];

        if ($item->getShipment()->isReturn()) {
            $view->vars['return_mode'] = true;
        } else {
            $view->vars['return_mode'] = false;

            if ($item->getAvailable() < $item->getExpected()) {
                FormUtil::addClass($view, 'danger');
            }
        }

        // Geocode
        $geocodes = [];
        $saleItem = $item->getSaleItem();
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
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var ShipmentItemInterface $item */
        $item = $view->vars['item'];

        $view->children['quantity']->vars['attr']['data-max'] = $item->getAvailable();

        if (0 < $options['level']) {
            $view->children['quantity']->vars['attr']['data-quantity'] = $item->getSaleItem()->getQuantity();
            $view->children['quantity']->vars['attr']['data-parent'] = $view->parent->parent->children['quantity']->vars['id'];
        }
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('level', 0)
            ->setDefault('data_class', $this->dataClass)
            ->setAllowedTypes('level', 'int');
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_shipment_item';
    }
}
