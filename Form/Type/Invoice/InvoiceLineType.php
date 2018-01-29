<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InvoiceLineType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceLineType extends ResourceFormType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', Type\NumberType::class, [
                'label'          => 'ekyna_core.field.quantity',
                'disabled'       => $options['disabled'] || 0 < $options['level'],
                'attr'           => [
                    'class' => 'input-sm',
                ],
                'error_bubbling' => true,
            ])
            ->add('children', InvoiceLinesType::class, [
                'entry_type'    => static::class,
                'entry_options' => [
                    'level'    => $options['level'] + 1,
                    'invoice'  => $options['invoice'],
                    'disabled' => $options['disabled'],
                ],
            ]);
    }

    /**
     * @inheritdoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var InvoiceLineInterface $line */
        $line = $form->getData();

        $view->vars['line'] = $line;
        $view->vars['level'] = $options['level'];
        $view->vars['credit_mode'] = InvoiceTypes::isCredit($options['invoice']->getType());

        $view->children['quantity']->vars['attr']['data-max'] = $line->getAvailable();

        if (0 < $options['level']) {
            $view->children['quantity']->vars['attr']['data-quantity'] = $line->getSaleItem()->getQuantity();
            $view->children['quantity']->vars['attr']['data-parent'] = $view->parent->parent->children['quantity']->vars['id'];
        }
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'level'      => 0,
                'data_class' => $this->dataClass,
                'invoice'    => null,
            ])
            ->setAllowedTypes('level', 'int')
            ->setAllowedTypes('invoice', InvoiceInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_invoice_line';
    }
}
