<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class ShipmentItemType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentItemType extends ResourceFormType
{
    /**
     * @var ShipmentCalculatorInterface
     */
    private $shipmentCalculator;


    /**
     * Sets the quantity calculator.
     *
     * @param ShipmentCalculatorInterface $calculator
     */
    public function setShipmentCalculator(ShipmentCalculatorInterface $calculator)
    {
        $this->shipmentCalculator = $calculator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('quantity', Type\NumberType::class, [
            'label' => 'ekyna_core.field.quantity',
            'attr' => [
                'class' => 'input-sm',
            ],
            'error_bubbling' => true,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /** @var ShipmentItemInterface $item */
        $item = $form->getData();

        $saleItem = $item->getSaleItem();

        $view->vars['designation'] = $saleItem->getDesignation();
        $view->vars['reference'] = $saleItem->getReference();

        if ($item->getShipment()->isReturn()) {
            $view->vars['return_mode'] = true;

            $returnable = $this->shipmentCalculator->calculateReturnableQuantity($item);
            $view->vars['returnable_quantity'] = $returnable;
        } else {
            $view->vars['return_mode'] = false;

            $expected = $this->shipmentCalculator->calculateShippableQuantity($item);
            $available = $this->shipmentCalculator->calculateAvailableQuantity($item);

            $view->vars['expected_quantity'] = $expected;
            $view->vars['available_quantity'] = $available;

            if ($available < $expected) {
                FormUtil::addClass($view, 'danger');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_shipment_item';
    }
}
