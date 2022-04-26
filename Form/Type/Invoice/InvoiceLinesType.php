<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class InvoiceLinesType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceLinesType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['headers'] = false;
        $view->vars['with_availability'] = $view->parent->vars['with_availability'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['entry_type'])
            ->setDefaults([
                'label' => t('invoice.field.lines', [], 'EkynaCommerce'),
            ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_invoice_lines';
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}
