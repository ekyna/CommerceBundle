<?php

declare(strict_types=1);

namespace Ekyna\Bundle\CommerceBundle\Form\Type\Invoice;

use Decimal\Decimal;
use Ekyna\Bundle\CommerceBundle\Form\FormHelper;
use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Component\Commerce\Common\Model\Units;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class InvoiceLineType
 * @package Ekyna\Bundle\CommerceBundle\Form\Type\Invoice
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceLineType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            /** @var InvoiceLineInterface $line */
            $line = $event->getData();

            $disabled = $options['disabled'] || $this->isDisabled($line);

            $unit = $line->getSaleItem()
                ? $line->getSaleItem()->getUnit()
                : Units::PIECE;

            FormHelper::addQuantityType($event->getForm(), $unit, [
                'disabled'       => $disabled,
                'error_bubbling' => true,
                'attr'           => [
                    'class' => 'input-sm',
                ],
            ]);

            $event
                ->getForm()
                ->add('children', InvoiceLinesType::class, [
                    'entry_type'    => static::class,
                    'entry_options' => [
                        'invoice'  => $options['invoice'],
                        'level'    => $options['level'] + 1,
                        'disabled' => $disabled,
                    ],
                ]);
        });
    }

    private function isDisabled(InvoiceLineInterface $line): bool
    {
        // Don't lock Shipment / discount line
        if ($line->getType() !== DocumentLineTypes::TYPE_GOOD) {
            return false;
        }

        $saleItem = $line->getSaleItem();

        if (null === $parent = $saleItem->getParent()) {
            return false;
        }

        return $parent->isPrivate() || ($parent->isCompound() && $parent->hasPrivateChildren());
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var InvoiceLineInterface $line */
        $line = $form->getData();
        /** @var InvoiceInterface $invoice */
        $invoice = $options['invoice'];

        $view->vars['line'] = $line;
        $view->vars['level'] = $options['level'];
        $view->vars['credit_mode'] = $invoice->isCredit();

        $view->children['quantity']->vars['attr']['data-max'] = $line->getAvailable() ?: New Decimal(0);

        if ($form->get('quantity')->isDisabled() && isset($view->parent->parent->children['quantity'])) {
            $view->children['quantity']->vars['attr']['data-quantity'] = $line->getSaleItem()->getQuantity();
            $view->children['quantity']->vars['attr']['data-parent'] = $view->parent->parent->children['quantity']->vars['id'];
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'level'   => 0,
                'invoice' => null,
            ])
            ->setAllowedTypes('level', 'int')
            ->setAllowedTypes('invoice', InvoiceInterface::class);
    }

    public function getBlockPrefix(): string
    {
        return 'ekyna_commerce_invoice_line';
    }
}
