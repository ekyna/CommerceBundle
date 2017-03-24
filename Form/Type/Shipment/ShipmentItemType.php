<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Shipment;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Ekyna\Component\Commerce\Shipment\Util\ShipmentUtil;
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

        $expected = ShipmentUtil::calculateExpectedQuantity($item);
        $available = ShipmentUtil::calculateAvailableQuantity($item);

        if ($available < $expected) {
            FormUtil::addClass($view, 'danger');
        }

        $view->vars['designation'] = $saleItem->getDesignation();
        $view->vars['reference'] = $saleItem->getReference();

        $view->vars['expected_quantity'] = $expected;
        $view->vars['available_quantity'] = $available;
    }

    /**
     * @inheritdoc
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
        return 'ekyna_commerce_shipment_item';
    }
}
