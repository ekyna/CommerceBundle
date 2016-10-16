<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class SupplierDeliveryItemType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryItemType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('quantity', NumberType::class, [
            'label' => 'ekyna_core.field.quantity',
            'attr' => [
                'class' => 'text-right'
            ],
            // TODO 'scale' => 2, // from packaging mode
        ]);
    }

    /**
     * @inheritdoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryItemInterface $deliveryItem */
        $deliveryItem = $form->getData();
        $orderItem = $deliveryItem->getOrderItem();

        $view->vars['designation'] = $orderItem->getDesignation();
        $view->vars['reference'] = $orderItem->getReference();
        $view->vars['remaining_quantity'] = $orderItem->getDeliveryRemainingQuantity($deliveryItem->getDelivery());
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_supplier_delivery_item';
    }

}
