<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Component\Commerce\Supplier\Util\SupplierUtil;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $builder
            ->add('quantity', NumberType::class, [
                'label'          => 'ekyna_core.field.quantity',
                'required'       => false,
                'attr'           => [
                    'class' => 'text-right',
                ],
                // TODO 'scale' => 2, // from packaging mode
                'error_bubbling' => true,
            ])
            ->add('geocode', TextType::class, [
                'label'          => 'ekyna_commerce.field.geocode',
                'required'       => false,
                'error_bubbling' => true,
                'attr' => [
                    'class' => 'geocode',
                ]
            ]);
    }

    /**
     * @inheritdoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryItemInterface $deliveryItem */
        $deliveryItem = $form->getData();

        $view->vars['order_item'] = $deliveryItem->getOrderItem();
        // TODO min attribute (aka sold/shipped to customers quantity)
        $view->vars['remaining_quantity'] = SupplierUtil::calculateDeliveryRemainingQuantity($deliveryItem);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('error_bubbling', false);
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_supplier_delivery_item';
    }

}
