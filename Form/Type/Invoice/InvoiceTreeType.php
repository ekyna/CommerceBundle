<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use ArrayAccess;
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
use Traversable;

use function array_replace;
use function Symfony\Component\Translation\t;

/**
 * Class InvoiceTreeType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceTreeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addModelTransformer(new InvoiceLinesDataTransformer($options['invoice']))
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                $data = $form->getNormData();

                if (null === $data) {
                    $data = [];
                }

                if (!is_array($data) && !($data instanceof Traversable && $data instanceof ArrayAccess)) {
                    throw new UnexpectedTypeException($data, 'array or (Traversable and ArrayAccess)');
                }

                // First remove all rows
                foreach ($form as $name => $child) {
                    $form->remove($name);
                }

                // Then add all rows again in the correct order
                foreach ($data as $name => $value) {
                    $form->add($name, $options['entry_type'], array_replace([
                        'property_path' => '[' . $name . ']',
                    ], $options['entry_options']));
                }
            });
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['headers'] = true;
        $view->vars['with_availability'] = $view->parent->vars['with_availability'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['entry_type'])
            ->setDefaults([
                'label'         => t('invoice.field.lines', [], 'EkynaCommerce'),
                'invoice'       => null,
                'entry_options' => [],
            ])
            ->setAllowedTypes('invoice', InvoiceInterface::class)
            ->setNormalizer('entry_options', function (Options $options, $value) {
                $value['invoice'] = $options['invoice'];
                $value['disabled'] = $options['disabled'];

                return $value;
            });
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_invoice_lines';
    }
}
