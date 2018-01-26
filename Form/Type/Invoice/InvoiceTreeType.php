<?php

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use Ekyna\Bundle\CommerceBundle\Form\DataTransformer\InvoiceLinesDataTransformer;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class InvoiceTreeType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceTreeType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addModelTransformer(new InvoiceLinesDataTransformer($options['invoice']))
            ->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) use ($options) {
                $form = $event->getForm();
                $data = $form->getNormData();

                if (null === $data) {
                    $data = array();
                }

                if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
                    throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
                }

                // First remove all rows
                foreach ($form as $name => $child) {
                    $form->remove($name);
                }

                // Then add all rows again in the correct order
                foreach ($data as $name => $value) {
                    $form->add($name, $options['entry_type'], array_replace(array(
                        'property_path' => '['.$name.']',
                    ), $options['entry_options']));
                }
            });
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['headers'] = true;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['entry_type'])
            ->setDefaults([
                'label'         => 'ekyna_commerce.invoice.field.lines',
                'invoice'       => null,
                'entry_options' => [],
            ])
            ->setAllowedTypes('invoice', InvoiceInterface::class)
            ->setNormalizer('entry_options', function(Options $options, $value) {
                $value['invoice'] = $options['invoice'];

                return $value;
            });
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'ekyna_commerce_invoice_lines';
    }
}