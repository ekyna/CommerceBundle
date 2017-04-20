<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Supplier;

use Ekyna\Bundle\CommerceBundle\Form\FormHelper;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Component\Commerce\Common\Model\Units;
use Ekyna\Component\Commerce\Supplier\Model\SupplierDeliveryItemInterface;
use Ekyna\Component\Commerce\Supplier\Util\SupplierUtil;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class SupplierDeliveryItemType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Supplier
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryItemType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('geocode', TextType::class, [
                'label'          => t('field.geocode', [], 'EkynaCommerce'),
                'required'       => false,
                'error_bubbling' => true,
                'attr'           => [
                    'class' => 'geocode',
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();

            $unit = Units::PIECE;
            if ($data instanceof SupplierDeliveryItemInterface) {
                $unit = $data->getOrderItem()->getUnit();
            }

            FormHelper::addQuantityType($event->getForm(), $unit, [
                'required'       => false,
                'error_bubbling' => true,
                'attr'           => [
                    'class' => 'text-right',
                ],
            ]);
        });
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var SupplierDeliveryItemInterface $deliveryItem */
        $deliveryItem = $form->getData();

        $view->vars['order_item'] = $deliveryItem->getOrderItem();
        // TODO min attribute (aka sold/shipped to customers quantity)
        $view->vars['remaining_quantity'] = SupplierUtil::calculateDeliveryRemainingQuantity($deliveryItem);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('error_bubbling', false);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_supplier_delivery_item';
    }

}
