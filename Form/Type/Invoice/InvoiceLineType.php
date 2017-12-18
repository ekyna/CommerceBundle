<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use Ekyna\Bundle\AdminBundle\Form\Type\ResourceFormType;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
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
                'disabled'       => 0 < $options['level'],
                'attr'           => [
                    'class' => 'input-sm',
                ],
                'error_bubbling' => true,
            ])
            ->add('children', InvoiceLinesType::class, [
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
        /** @var InvoiceLineInterface $line */
        $line = $form->getData();

        $view->vars['line'] = $line;
        $view->vars['level'] = $options['level'];
    }

    /**
     * @inheritdoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var InvoiceLineInterface $line */
        $line = $view->vars['line'];

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
            ->setDefault('level', 0)
            ->setDefault('data_class', $this->dataClass)
            ->setAllowedTypes('level', 'int');
    }

    /**
     * @inheritdoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_invoice_line';
    }
}
