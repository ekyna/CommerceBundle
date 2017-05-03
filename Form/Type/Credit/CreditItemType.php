<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Credit;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Component\Commerce\Credit\Model\CreditItemInterface;
use Ekyna\Component\Commerce\Credit\Util\CreditUtil;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class CreditItemType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Credit
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CreditItemType extends ResourceFormType
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
        /** @var CreditItemInterface $item */
        $item = $form->getData();

        $saleItem = $item->getSaleItem();

        $view->vars['designation'] = $saleItem->getDesignation();
        $view->vars['reference'] = $saleItem->getReference();

        $available = CreditUtil::calculateCreditableQuantity($item);

        $view->vars['available_quantity'] = $available;
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_credit_item';
    }
}
